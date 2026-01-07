<?php

namespace Tests\Unit;

use App\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserve_decreases_available_and_increases_reserved(): void
    {
        $inventory = Inventory::create([
            'sku' => 'TEST123',
            'available_qty' => 100,
            'reserved_qty' => 0,
        ]);

        $result = $inventory->reserve(30);

        $this->assertTrue($result);
        $inventory->refresh();
        $this->assertEquals(70, $inventory->available_qty);
        $this->assertEquals(30, $inventory->reserved_qty);
    }

    public function test_reserve_returns_false_when_not_enough(): void
    {
        $inventory = Inventory::create([
            'sku' => 'TEST123',
            'available_qty' => 10,
            'reserved_qty' => 0,
        ]);

        $result = $inventory->reserve(30);

        $this->assertFalse($result);
        $inventory->refresh();
        $this->assertEquals(10, $inventory->available_qty);
        $this->assertEquals(0, $inventory->reserved_qty);
    }

    public function test_release_increases_available_and_decreases_reserved(): void
    {
        $inventory = Inventory::create([
            'sku' => 'TEST123',
            'available_qty' => 50,
            'reserved_qty' => 30,
        ]);

        $inventory->release(20);

        $inventory->refresh();
        $this->assertEquals(70, $inventory->available_qty);
        $this->assertEquals(10, $inventory->reserved_qty);
    }

    public function test_has_enough_returns_true_when_sufficient(): void
    {
        $inventory = Inventory::create([
            'sku' => 'TEST123',
            'available_qty' => 100,
            'reserved_qty' => 0,
        ]);

        $this->assertTrue($inventory->hasEnough(50));
        $this->assertTrue($inventory->hasEnough(100));
    }

    public function test_has_enough_returns_false_when_insufficient(): void
    {
        $inventory = Inventory::create([
            'sku' => 'TEST123',
            'available_qty' => 50,
            'reserved_qty' => 0,
        ]);

        $this->assertFalse($inventory->hasEnough(100));
    }

    public function test_get_total_qty(): void
    {
        $inventory = Inventory::create([
            'sku' => 'TEST123',
            'available_qty' => 70,
            'reserved_qty' => 30,
        ]);

        $this->assertEquals(100, $inventory->getTotalQty());
    }
}

