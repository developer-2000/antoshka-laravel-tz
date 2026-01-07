<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        
        Order::insert([
            [
                'sku' => 'ABC123',
                'qty' => 10,
                'status' => Order::STATUS_PENDING,
                'supplier_check_attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'XYZ789',
                'qty' => 5,
                'status' => Order::STATUS_RESERVED,
                'supplier_check_attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'DEF456',
                'qty' => 30,
                'status' => Order::STATUS_AWAITING_RESTOCK,
                'supplier_check_attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'GHI012',
                'qty' => 15,
                'status' => Order::STATUS_FAILED,
                'supplier_check_attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'JKL345',
                'qty' => 3,
                'status' => Order::STATUS_PENDING,
                'supplier_check_attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'MNO678',
                'qty' => 20,
                'status' => Order::STATUS_RESERVED,
                'supplier_check_attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'PQR901',
                'qty' => 7,
                'status' => Order::STATUS_PENDING,
                'supplier_check_attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'STU234',
                'qty' => 12,
                'status' => Order::STATUS_AWAITING_RESTOCK,
                'supplier_check_attempts' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'VWX567',
                'qty' => 8,
                'status' => Order::STATUS_RESERVED,
                'supplier_check_attempts' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sku' => 'YZA890',
                'qty' => 25,
                'status' => Order::STATUS_FAILED,
                'supplier_check_attempts' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
