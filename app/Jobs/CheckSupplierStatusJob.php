<?php

namespace App\Jobs;

use App\Exceptions\SupplierException;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Services\SupplierService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckSupplierStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $orderId;
    public $tries = 1;
    public $timeout = 90;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(SupplierService $supplierService): void
    {
        try {
            $order = Order::find($this->orderId);

            if (!$order) {
                Log::error('Order not found in CheckSupplierStatusJob', [
                    'order_id' => $this->orderId,
                ]);
                return;
            }

            if (empty($order->supplier_ref)) {
                Log::error('Supplier ref is empty', [
                    'order_id' => $order->id,
                ]);
                return;
            }

            if ($order->supplier_check_attempts >= 2) {
                Log::warning('Maximum check attempts reached', [
                    'order_id' => $order->id,
                    'attempts' => $order->supplier_check_attempts,
                ]);
                return;
            }

            try {
                $status = $supplierService->checkStatus($order->supplier_ref);
            } catch (SupplierException $e) {
                $order->supplier_check_attempts++;
                if ($order->supplier_check_attempts >= 2) {
                    $order->status = Order::STATUS_FAILED;
                    Log::info('Supplier check failed after max attempts', [
                        'order_id' => $order->id,
                    ]);
                }
                $order->save();
                throw $e;
            }

            if ($status === 'ok') {
                DB::transaction(function () use ($order) {
                    $inventory = Inventory::firstOrCreate(
                        ['sku' => $order->sku],
                        ['available_qty' => 0, 'reserved_qty' => 0]
                    );

                    $qtyBeforeRestock = $inventory->available_qty;
                    $inventory->available_qty += $order->qty;
                    $inventory->save();

                    InventoryMovement::create([
                        'inventory_id' => $inventory->id,
                        'order_id' => $order->id,
                        'sku' => $order->sku,
                        'movement_type' => 'restock',
                        'qty' => $order->qty,
                        'qty_before' => $qtyBeforeRestock,
                        'qty_after' => $inventory->available_qty,
                    ]);

                    $qtyBeforeReserve = $inventory->available_qty;
                    $inventory->reserve($order->qty);

                    InventoryMovement::create([
                        'inventory_id' => $inventory->id,
                        'order_id' => $order->id,
                        'sku' => $order->sku,
                        'movement_type' => 'reserve',
                        'qty' => $order->qty,
                        'qty_before' => $qtyBeforeReserve,
                        'qty_after' => $inventory->available_qty,
                    ]);

                    $order->status = Order::STATUS_RESERVED;
                    $order->save();

                    Log::info('Order reserved', [
                        'order_id' => $order->id,
                    ]);
                });
            } elseif ($status === 'fail') {
                $order->status = Order::STATUS_FAILED;
                $order->save();

                Log::info('Supplier reservation failed', [
                    'order_id' => $order->id,
                ]);
            } elseif ($status === 'delayed') {
                $order->supplier_check_attempts++;
                $order->save();

                if ($order->supplier_check_attempts < 2) {
                    CheckSupplierStatusJob::dispatch($order->id)->delay(15);
                } else {
                    $order->status = Order::STATUS_FAILED;
                    $order->save();

                    Log::info('Supplier check failed after max attempts', [
                        'order_id' => $order->id,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in CheckSupplierStatusJob', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CheckSupplierStatusJob failed', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $order = Order::find($this->orderId);
        if ($order) {
            $order->status = Order::STATUS_FAILED;
            $order->save();
        }
    }
}
