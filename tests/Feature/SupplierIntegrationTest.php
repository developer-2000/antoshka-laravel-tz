<?php

namespace Tests\Feature;

use App\Jobs\CheckSupplierStatusJob;
use App\Jobs\RequestSupplierReservationJob;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Helpers\SupplierApiHelper;
use Tests\TestCase;

class SupplierIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_supplier_delivery_ok(): void
    {
        Inventory::create([
            'sku' => 'TEST123',
            'available_qty' => 10,
            'reserved_qty' => 0,
        ]);

        $order = Order::create([
            'sku' => 'TEST123',
            'qty' => 30,
            'status' => Order::STATUS_AWAITING_RESTOCK,
            'supplier_ref' => 'SUP-20240106001',
        ]);

        SupplierApiHelper::fakeStatusOk('SUP-20240106001');

        CheckSupplierStatusJob::dispatchSync($order->id);

        $order->refresh();
        $this->assertEquals(Order::STATUS_RESERVED, $order->status);

        $inventory = Inventory::where('sku', 'TEST123')->first();
        $inventory->refresh();
        $this->assertEquals(10, $inventory->available_qty);
        $this->assertEquals(30, $inventory->reserved_qty);
    }

    public function test_supplier_delivery_fail(): void
    {
        $order = Order::create([
            'sku' => 'TEST123',
            'qty' => 30,
            'status' => Order::STATUS_AWAITING_RESTOCK,
            'supplier_ref' => 'SUP-20240106001',
        ]);

        SupplierApiHelper::fakeStatusFail('SUP-20240106001');

        CheckSupplierStatusJob::dispatchSync($order->id);

        $order->refresh();
        $this->assertEquals(Order::STATUS_FAILED, $order->status);
    }

    public function test_supplier_delivery_delayed_then_ok(): void
    {
        Inventory::create([
            'sku' => 'TEST123',
            'available_qty' => 10,
            'reserved_qty' => 0,
        ]);

        $order = Order::create([
            'sku' => 'TEST123',
            'qty' => 30,
            'status' => Order::STATUS_AWAITING_RESTOCK,
            'supplier_ref' => 'SUP-20240106002',
            'supplier_check_attempts' => 0,
        ]);

        \Illuminate\Support\Facades\Cache::put('supplier_status_SUP-20240106002', 'delayed', 3600);

        SupplierApiHelper::fakeStatusDelayed('SUP-20240106002');

        $job = new \App\Jobs\CheckSupplierStatusJob($order->id);
        $job->handle(app(\App\Services\SupplierService::class));

        $order = Order::find($order->id);
        $this->assertGreaterThanOrEqual(1, $order->supplier_check_attempts);

        $order2 = Order::create([
            'sku' => 'TEST123',
            'qty' => 30,
            'status' => Order::STATUS_AWAITING_RESTOCK,
            'supplier_ref' => 'SUP-20240106003',
            'supplier_check_attempts' => 0,
        ]);

        \Illuminate\Support\Facades\Cache::put('supplier_status_SUP-20240106003', 'ok', 3600);

        SupplierApiHelper::fakeStatusOk('SUP-20240106003');

        $job2 = new \App\Jobs\CheckSupplierStatusJob($order2->id);
        $job2->handle(app(\App\Services\SupplierService::class));

        $order2 = Order::find($order2->id);
        $this->assertEquals(Order::STATUS_RESERVED, $order2->status);
    }

    public function test_supplier_delivery_delayed_max_attempts(): void
    {
        Inventory::create([
            'sku' => 'TEST123',
            'available_qty' => 10,
            'reserved_qty' => 0,
        ]);

        $order = Order::create([
            'sku' => 'TEST123',
            'qty' => 30,
            'status' => Order::STATUS_AWAITING_RESTOCK,
            'supplier_ref' => 'SUP-20240106001',
            'supplier_check_attempts' => 0,
        ]);

        \Illuminate\Support\Facades\Cache::put('supplier_status_SUP-20240106001', 'delayed', 3600);

        SupplierApiHelper::fakeStatusDelayed('SUP-20240106001');

        $job = new \App\Jobs\CheckSupplierStatusJob($order->id);
        $job->handle(app(\App\Services\SupplierService::class));
        $order = Order::find($order->id);
        $this->assertGreaterThanOrEqual(1, $order->supplier_check_attempts);

        $job = new \App\Jobs\CheckSupplierStatusJob($order->id);
        $job->handle(app(\App\Services\SupplierService::class));
        $order = Order::find($order->id);
        $this->assertEquals(2, $order->supplier_check_attempts);
        $this->assertEquals(Order::STATUS_FAILED, $order->status);
    }
}

