<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierReserveRequest;
use App\Services\SupplierApiService;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для эмуляции API поставщика
 */
class SupplierController extends Controller
{
    /**
     * @param SupplierApiService $supplierApiService
     */
    public function __construct(
        private SupplierApiService $supplierApiService
    ) {
    }

    /**
     * Создать резервацию у поставщика
     *
     * @param SupplierReserveRequest $request
     * @return JsonResponse
     */
    public function reserve(SupplierReserveRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $result = $this->supplierApiService->createReservation(
            $validated['sku'],
            $validated['qty']
        );

        return response()->json($result, 200);
    }

    /**
     * Получить статус резервации
     *
     * @param string $ref Референс резервации
     * @return JsonResponse
     */
    public function status(string $ref): JsonResponse
    {
        if (!$this->supplierApiService->validateRefFormat($ref)) {
            return response()->json([
                'error' => 'Неверный формат ref',
            ], 400);
        }

        $status = $this->supplierApiService->getStatus($ref);

        return response()->json([
            'status' => $status,
        ], 200);
    }
}
