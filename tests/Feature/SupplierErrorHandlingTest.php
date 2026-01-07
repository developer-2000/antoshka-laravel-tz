<?php

namespace Tests\Feature;

use App\Exceptions\SupplierException;
use App\Jobs\RequestSupplierReservationJob;
use App\Jobs\CheckSupplierStatusJob;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\SupplierApiHelper;
use Tests\TestCase;

class SupplierErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_timeout_error(): void
    {
        Inventory::create([
            'sku' => 'ERROR123',
            'available_qty' => 10,
            'reserved_qty' => 0,
        ]);

        $order = Order::create([
            'sku' => 'ERROR123',
            'qty' => 50,
            'status' => Order::STATUS_AWAITING_RESTOCK,
        ]);

        SupplierApiHelper::fakeTimeout();

        $job = new RequestSupplierReservationJob($order->id);

        try {
            $job->handle(app(\App\Services\SupplierService::class));
            $this->fail('Expected SupplierException was not thrown');
        } catch (SupplierException $e) {
            $order->refresh();
            $this->assertEquals(Order::STATUS_FAILED, $order->status);
        } catch (\Exception $e) {
            $order->refresh();
            $this->assertEquals(Order::STATUS_FAILED, $order->status);
        }
    }

    public function test_supplier_network_error(): void
    {
        Inventory::create([
            'sku' => 'ERROR456',
            'available_qty' => 10,
            'reserved_qty' => 0,
        ]);

        $order = Order::create([
            'sku' => 'ERROR456',
            'qty' => 50,
            'status' => Order::STATUS_AWAITING_RESTOCK,
        ]);

        SupplierApiHelper::fakeNetworkError();

        $job = new RequestSupplierReservationJob($order->id);

        $this->expectException(SupplierException::class);

        try {
            $job->handle(app(\App\Services\SupplierService::class));
        } catch (SupplierException $e) {
            $order->refresh();
            $this->assertEquals(Order::STATUS_FAILED, $order->status);
            throw $e;
        }
    }

    public function test_supplier_invalid_response(): void
    {
        Inventory::create([
            'sku' => 'ERROR789',
            'available_qty' => 10,
            'reserved_qty' => 0,
        ]);

        $order = Order::create([
            'sku' => 'ERROR789',
            'qty' => 50,
            'status' => Order::STATUS_AWAITING_RESTOCK,
            'supplier_ref' => 'SUP-20240106099',
        ]);

        SupplierApiHelper::fakeInvalidResponse();

        $job = new CheckSupplierStatusJob($order->id);

        try {
            $job->handle(app(\App\Services\SupplierService::class));
        } catch (\Exception $e) {
            $order->refresh();
            $this->assertNotEquals(Order::STATUS_RESERVED, $order->status);
        }
    }
}

