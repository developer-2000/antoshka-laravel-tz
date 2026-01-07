<?php

namespace App\Jobs;

use App\Jobs\RequestSupplierReservationJob;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Джоба для резервирования товара на складе
 * 
 * Обрабатывает резервирование товара для заказа:
 * - Проверяет наличие товара на складе
 * - Резервирует товар если его достаточно
 * - Запрашивает товар у поставщика если его недостаточно
 */
class ReserveInventoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * ID заказа для резервирования
     *
     * @var int
     */
    public int $orderId;

    /**
     * Количество попыток выполнения джобы
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Таймаут выполнения джобы в секундах
     *
     * @var int
     */
    public $timeout = 90;

    /**
     * @param int $orderId ID заказа
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Обработка резервирования товара на складе
     * 
     * Процесс:
     * 1. Получает заказ с блокировкой (lockForUpdate) для предотвращения race conditions
     * 2. Проверяет что заказ существует и в статусе "pending"
     * 3. Получает или создает запись Inventory по SKU
     * 4. Проверяет достаточность запасов через hasEnough()
     * 
     * Если товара достаточно:
     * - В транзакции резервирует товар (уменьшает available_qty, увеличивает reserved_qty)
     * - Обновляет статус заказа на "reserved"
     * - Создает запись в InventoryMovements для истории
     * 
     * Если товара недостаточно:
     * - Обновляет статус заказа на "awaiting_restock"
     * - Запускает RequestSupplierReservationJob для запроса у поставщика
     *
     * @return void
     * @throws \Exception
     */
    public function handle(): void
    {
        try {
            // 1. Получаем заказ с блокировкой для предотвращения одновременной обработки
            $order = Order::lockForUpdate()->find($this->orderId);

            // 2. Проверяем что заказ существует
            if (!$order) {
                Log::error('Order not found', ['order_id' => $this->orderId]);
                return;
            }

            // 3. Проверяем что заказ в статусе "pending" (еще не обработан)
            if ($order->status !== Order::STATUS_PENDING) {
                return;
            }

            // 4. Получаем или создаем запись Inventory по SKU
            $inventory = Inventory::firstOrCreate(
                ['sku' => $order->sku],
                ['available_qty' => 0, 'reserved_qty' => 0]
            );
            $inventory->refresh();

            // 5. Проверяем достаточность запасов
            if ($inventory->hasEnough($order->qty)) {
                // 6. Товара достаточно - резервируем в транзакции
                DB::transaction(function () use ($order, $inventory) {
                    $qtyBefore = $inventory->available_qty;

                    // Резервируем товар (уменьшаем available_qty, увеличиваем reserved_qty)
                    if (!$inventory->reserve($order->qty)) {
                        throw new \Exception('Failed to reserve inventory');
                    }

                    // Обновляем статус заказа на "reserved"
                    $order->status = Order::STATUS_RESERVED;
                    $order->save();

                    // Создаем запись в истории движений склада
                    InventoryMovement::create([
                        'inventory_id' => $inventory->id,
                        'order_id' => $order->id,
                        'sku' => $order->sku,
                        'movement_type' => 'reserve',
                        'qty' => $order->qty,
                        'qty_before' => $qtyBefore,
                        'qty_after' => $inventory->available_qty,
                    ]);

                    Log::info('Inventory reserved successfully', [
                        'order_id' => $order->id,
                        'sku' => $order->sku,
                        'qty' => $order->qty,
                    ]);
                });
            } else {
                // 7. Товара недостаточно - запрашиваем у поставщика
                // Обновляем статус в транзакции с блокировкой для предотвращения дублирования
                DB::transaction(function () use ($order) {
                    // Перезагружаем заказ с блокировкой внутри транзакции
                    $order = Order::lockForUpdate()->find($this->orderId);
                    
                    if (!$order) {
                        return;
                    }
                    
                    // Двойная проверка статуса после блокировки
                    if ($order->status !== Order::STATUS_PENDING) {
                        return;
                    }
                    
                    // Обновляем статус на "awaiting_restock"
                    $order->status = Order::STATUS_AWAITING_RESTOCK;
                    $order->save();
                });

                // Перезагружаем заказ после транзакции
                $order->refresh();
                
                // Проверяем, что статус действительно обновлен
                if ($order->status !== Order::STATUS_AWAITING_RESTOCK) {
                    return;
                }

                Log::info('Insufficient inventory, requesting supplier', [
                    'order_id' => $order->id,
                    'sku' => $order->sku,
                    'qty' => $order->qty,
                ]);

                // Запускаем джобу для запроса у поставщика
                RequestSupplierReservationJob::dispatch($order->id);
            }
        } catch (\Exception $e) {
            Log::error('Error in ReserveInventoryJob', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Обработка неудачного выполнения джобы
     *
     * @param \Throwable $exception Исключение, которое привело к провалу джобы
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ReserveInventoryJob failed', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
