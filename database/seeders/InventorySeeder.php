<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        
        Inventory::insert([
            [
                'sku' => 'ABC123',
                'available_qty' => 100,
                'reserved_qty' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'XYZ789',
                'available_qty' => 50,
                'reserved_qty' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'DEF456',
                'available_qty' => 25,
                'reserved_qty' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'GHI012',
                'available_qty' => 10,
                'reserved_qty' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'JKL345',
                'available_qty' => 5,
                'reserved_qty' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'MNO678',
                'available_qty' => 80,
                'reserved_qty' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'PQR901',
                'available_qty' => 40,
                'reserved_qty' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'STU234',
                'available_qty' => 15,
                'reserved_qty' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'VWX567',
                'available_qty' => 60,
                'reserved_qty' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'YZA890',
                'available_qty' => 30,
                'reserved_qty' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
