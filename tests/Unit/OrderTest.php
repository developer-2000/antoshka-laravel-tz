<?php

namespace Tests\Unit;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order(): void
    {
        $order = Order::create([
            'sku' => 'TEST123',
            'qty' => 10,
            'status' => Order::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'sku' => 'TEST123',
            'qty' => 10,
            'status' => Order::STATUS_PENDING,
        ]);
    }

    public function test_status_constants(): void
    {
        $this->assertEquals('pending', Order::STATUS_PENDING);
        $this->assertEquals('reserved', Order::STATUS_RESERVED);
        $this->assertEquals('awaiting_restock', Order::STATUS_AWAITING_RESTOCK);
        $this->assertEquals('failed', Order::STATUS_FAILED);
    }

    public function test_scope_pending(): void
    {
        Order::create(['sku' => 'SKU1', 'qty' => 1, 'status' => Order::STATUS_PENDING]);
        Order::create(['sku' => 'SKU2', 'qty' => 2, 'status' => Order::STATUS_RESERVED]);

        $pendingOrders = Order::pending()->get();

        $this->assertCount(1, $pendingOrders);
        $this->assertEquals(Order::STATUS_PENDING, $pendingOrders->first()->status);
    }

    public function test_scope_reserved(): void
    {
        Order::create(['sku' => 'SKU1', 'qty' => 1, 'status' => Order::STATUS_RESERVED]);
        Order::create(['sku' => 'SKU2', 'qty' => 2, 'status' => Order::STATUS_PENDING]);

        $reservedOrders = Order::reserved()->get();

        $this->assertCount(1, $reservedOrders);
        $this->assertEquals(Order::STATUS_RESERVED, $reservedOrders->first()->status);
    }

    public function test_scope_awaiting_restock(): void
    {
        Order::create(['sku' => 'SKU1', 'qty' => 1, 'status' => Order::STATUS_AWAITING_RESTOCK]);
        Order::create(['sku' => 'SKU2', 'qty' => 2, 'status' => Order::STATUS_PENDING]);

        $awaitingOrders = Order::awaitingRestock()->get();

        $this->assertCount(1, $awaitingOrders);
        $this->assertEquals(Order::STATUS_AWAITING_RESTOCK, $awaitingOrders->first()->status);
    }

    public function test_scope_failed(): void
    {
        Order::create(['sku' => 'SKU1', 'qty' => 1, 'status' => Order::STATUS_FAILED]);
        Order::create(['sku' => 'SKU2', 'qty' => 2, 'status' => Order::STATUS_PENDING]);

        $failedOrders = Order::failed()->get();

        $this->assertCount(1, $failedOrders);
        $this->assertEquals(Order::STATUS_FAILED, $failedOrders->first()->status);
    }
}

