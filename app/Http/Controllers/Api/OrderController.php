<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Контроллер для работы с заказами через API
 */
class OrderController extends Controller
{
    /**
     * @param OrderService $orderService
     */
    public function __construct(
        private OrderService $orderService
    ) {
    }

    /**
     * Создать новый заказ
     * @param StoreOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $order = $this->orderService->createOrder(
                $validated['sku'],
                $validated['qty']
            );

            return response()->json(
                $this->orderService->formatOrderForCreate($order),
                201
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка при создании заказа',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получить заказ по ID
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getOrder($id);

            return response()->json(
                $this->orderService->formatOrderForShow($order),
                200
            );
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Заказ не найден',
                'message' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка при получении заказа',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
