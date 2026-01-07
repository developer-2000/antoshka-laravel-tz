<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Inventory;

/**
 * Сервис для работы с данными дашборда
 */
class DashboardService
{
    /**
     * Получить данные для дашборда
     *
     * @return array Массив данных дашборда
     */
    public function getDashboardData(): array
    {
        // Получаем все подсчеты заказов в одном запросе
        $orderStats = Order::selectRaw('
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_orders,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as reserved_orders
        ', [Order::STATUS_PENDING, Order::STATUS_RESERVED])->first();

        // Получаем последние заказы и общее количество инвентаря параллельно
        $recentOrders = Order::orderBy('created_at', 'desc')->limit(5)->get();
        $totalInventory = Inventory::count();

        return [
            'totalOrders' => (int) $orderStats->total_orders,
            'pendingOrders' => (int) $orderStats->pending_orders,
            'reservedOrders' => (int) $orderStats->reserved_orders,
            'totalInventory' => $totalInventory,
            'recentOrders' => $recentOrders,
        ];
    }
}

