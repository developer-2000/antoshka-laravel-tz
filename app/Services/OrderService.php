<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для работы с заказами
 */
class OrderService
{
    /**
     * Создать новый заказ
     *
     * @param string $sku Артикул товара
     * @param int $qty Количество товара
     * @return Order Созданный заказ
     * @throws \Exception
     */
    public function createOrder(string $sku, int $qty): Order
    {
        try {
            $order = Order::create([
                'sku' => $sku,
                'qty' => $qty,
                'status' => Order::STATUS_PENDING,
            ]);

            // Start Event создания заказа, асинхронно запуская резервирование товара через ReserveInventoryJob
            Event::dispatch(new OrderCreated($order));

            return $order;
        } catch (\Exception $e) {
            Log::error('Ошибка при создании заказа', [
                'sku' => $sku,
                'qty' => $qty,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Получить заказ по ID
     *
     * @param int $id ID заказа
     * @return Order Заказ
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getOrder(int $id): Order
    {
        return Order::findOrFail($id);
    }

    /**
     * Форматировать заказ для ответа при создании
     *
     * @param Order $order Заказ
     * @return array Массив данных заказа
     */
    public function formatOrderForCreate(Order $order): array
    {
        return [
            'id' => $order->id,
            'sku' => $order->sku,
            'qty' => $order->qty,
            'status' => $order->status,
            'created_at' => $order->created_at,
        ];
    }

    /**
     * Форматировать заказ для ответа при просмотре
     *
     * @param Order $order Заказ
     * @return array Массив данных заказа
     */
    public function formatOrderForShow(Order $order): array
    {
        return [
            'id' => $order->id,
            'sku' => $order->sku,
            'qty' => $order->qty,
            'status' => $order->status,
            'supplier_ref' => $order->supplier_ref,
            'supplier_check_attempts' => $order->supplier_check_attempts,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    }

    /**
     * Получить список заказов с фильтрацией
     *
     * @param string|null $status Статус заказа для фильтрации
     * @param string|null $search Поисковый запрос по SKU
     * @param int $perPage Количество элементов на странице
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getOrdersList(?string $status = null, ?string $search = null, int $perPage = 5)
    {
        $query = Order::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where('sku', 'like', '%' . $search . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Получить заказ с движениями инвентаря
     *
     * @param int $id ID заказа
     * @return array Массив с данными: order, movements
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getOrderWithMovements(int $id): array
    {
        $order = Order::findOrFail($id);
        $movements = InventoryMovement::where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'order' => $order,
            'movements' => $movements,
        ];
    }
}

