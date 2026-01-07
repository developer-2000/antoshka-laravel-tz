<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Jobs\ReserveInventoryJob;
use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class FullFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_flow_order_creation_to_reservation(): void
    {
        Inventory::create([
            'sku' => 'FLOW123',
            'available_qty' => 100,
            'reserved_qty' => 0,
        ]);

        $response = $this->postJson('/api/order', [
            'sku' => 'FLOW123',
            'qty' => 50,
        ]);

        $response->assertStatus(201);
        $orderData = $response->json();

        $order = Order::find($orderData['id']);
        $this->assertNotNull($order);
        $this->assertEquals(Order::STATUS_PENDING, $order->status);

        // Обрабатываем событие синхронно (имитируем работу Listener)
        $event = new OrderCreated($order);
        $event->handle();

        // Обрабатываем Job синхронно
        $job = new ReserveInventoryJob($order->id);
        $job->handle();

        $order->refresh();
        $this->assertEquals(Order::STATUS_RESERVED, $order->status);

        $inventory = Inventory::where('sku', 'FLOW123')->first();
        $inventory->refresh();
        $this->assertEquals(50, $inventory->available_qty);
        $this->assertEquals(50, $inventory->reserved_qty);
    }

    public function test_full_flow_order_creation_to_supplier_request(): void
    {
        Inventory::create([
            'sku' => 'FLOW456',
            'available_qty' => 10,
            'reserved_qty' => 0,
        ]);

        $response = $this->postJson('/api/order', [
            'sku' => 'FLOW456',
            'qty' => 50,
        ]);

        $response->assertStatus(201);
        $orderData = $response->json();

        $order = Order::find($orderData['id']);
        $this->assertNotNull($order);
        $this->assertEquals(Order::STATUS_PENDING, $order->status);

        // Обрабатываем событие синхронно (имитируем работу Listener)
        $event = new OrderCreated($order);
        $event->handle();

        // Обрабатываем Job синхронно
        $job = new ReserveInventoryJob($order->id);
        $job->handle();

        $order->refresh();
        $this->assertEquals(Order::STATUS_AWAITING_RESTOCK, $order->status);

        // Проверяем, что заказ перешел в статус awaiting_restock
        // (это означает, что RequestSupplierReservationJob должен был быть запущен)
        // В реальной системе это происходит асинхронно, но в тесте мы проверяем результат
    }
}

