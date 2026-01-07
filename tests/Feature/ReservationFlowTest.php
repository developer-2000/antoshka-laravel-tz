<?php

namespace Tests\Feature;

use App\Jobs\ReserveInventoryJob;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_reservation_flow(): void
    {
        Inventory::create([
            'sku' => 'TEST123',
            'available_qty' => 100,
            'reserved_qty' => 0,
        ]);

        $order = Order::create([
            'sku' => 'TEST123',
            'qty' => 30,
            'status' => Order::STATUS_PENDING,
        ]);

        ReserveInventoryJob::dispatchSync($order->id);

        $order->refresh();
        $this->assertEquals(Order::STATUS_RESERVED, $order->status);

        $inventory = Inventory::where('sku', 'TEST123')->first();
        $inventory->refresh();
        $this->assertEquals(70, $inventory->available_qty);
        $this->assertEquals(30, $inventory->reserved_qty);

        $movement = InventoryMovement::where('order_id', $order->id)
            ->where('movement_type', 'reserve')
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(30, $movement->qty);
    }

    public function test_insufficient_inventory_flow(): void
    {
        Queue::fake();

        Inventory::create([
            'sku' => 'TEST123',
            'available_qty' => 10,
            'reserved_qty' => 0,
        ]);

        $order = Order::create([
            'sku' => 'TEST123',
            'qty' => 30,
            'status' => Order::STATUS_PENDING,
        ]);

        $job = new \App\Jobs\ReserveInventoryJob($order->id);
        $job->handle();

        $order->refresh();
        $this->assertEquals(Order::STATUS_AWAITING_RESTOCK, $order->status);

        Queue::assertPushed(\App\Jobs\RequestSupplierReservationJob::class);
    }
}

