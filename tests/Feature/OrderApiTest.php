<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order_successfully(): void
    {
        Event::fake();

        $response = $this->postJson('/api/order', [
            'sku' => 'TEST123',
            'qty' => 10,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'sku',
                'qty',
                'status',
                'created_at',
            ]);

        $this->assertDatabaseHas('orders', [
            'sku' => 'TEST123',
            'qty' => 10,
            'status' => Order::STATUS_PENDING,
        ]);

        Event::assertDispatched(OrderCreated::class);
    }

    public function test_validation_fails_with_empty_sku(): void
    {
        $response = $this->postJson('/api/order', [
            'qty' => 10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_validation_fails_with_invalid_qty(): void
    {
        $response = $this->postJson('/api/order', [
            'sku' => 'TEST123',
            'qty' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['qty']);
    }

    public function test_can_get_order_by_id(): void
    {
        $order = Order::create([
            'sku' => 'TEST123',
            'qty' => 10,
            'status' => Order::STATUS_PENDING,
        ]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $order->id,
                'sku' => 'TEST123',
                'qty' => 10,
                'status' => Order::STATUS_PENDING,
            ]);
    }

    public function test_returns_404_for_nonexistent_order(): void
    {
        $response = $this->getJson('/api/orders/99999');

        $response->assertStatus(404);
    }
}

