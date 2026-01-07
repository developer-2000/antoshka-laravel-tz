<?php

namespace Tests\Feature;

use App\Models\InventoryMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_movements_history(): void
    {
        InventoryMovement::create([
            'sku' => 'TEST123',
            'movement_type' => 'reserve',
            'qty' => 10,
            'qty_before' => 100,
            'qty_after' => 90,
        ]);

        InventoryMovement::create([
            'sku' => 'TEST123',
            'movement_type' => 'release',
            'qty' => 5,
            'qty_before' => 90,
            'qty_after' => 95,
        ]);

        $response = $this->getJson('/api/inventory/TEST123/movements');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'sku',
                    'movement_type',
                    'qty',
                    'qty_before',
                    'qty_after',
                ],
            ]);
    }

    public function test_returns_empty_array_when_no_movements(): void
    {
        $response = $this->getJson('/api/inventory/NONEXISTENT/movements');

        $response->assertStatus(200)
            ->assertJson([]);
    }
}

