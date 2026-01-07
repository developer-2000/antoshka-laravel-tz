<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderIndexRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Контроллер для работы с заказами
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
     * Отобразить список заказов
     *
     * @param OrderIndexRequest $request
     * @return View
     */
    public function index(OrderIndexRequest $request): View
    {
        $validated = $request->validated();
        
        $orders = $this->orderService->getOrdersList(
            $validated['status'] ?? null,
            $validated['search'] ?? null
        );

        return view('orders.index', compact('orders'));
    }

    /**
     * Отобразить форму создания заказа
     *
     * @return View
     */
    public function create(): View
    {
        return view('orders.create');
    }

    /**
     * Создать новый заказ
     *
     * @param StoreOrderRequest $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $validated = $request->validated();
            
            $order = $this->orderService->createOrder(
                $validated['sku'],
                $validated['qty']
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Заказ успешно создан',
                    'order' => $order,
                ], 201);
            }

            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Заказ успешно создан');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при создании заказа: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => 'Ошибка при создании заказа: ' . $e->getMessage()]);
        }
    }

    /**
     * Отобразить детали заказа
     *
     * @param int $id ID заказа
     * @return View
     */
    public function show(int $id): View
    {
        $data = $this->orderService->getOrderWithMovements($id);

        return view('orders.show', $data);
    }
}
