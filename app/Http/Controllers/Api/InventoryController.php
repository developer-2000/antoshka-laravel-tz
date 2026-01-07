<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InventoryMovementsRequest;
use App\Models\InventoryMovement;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для работы с инвентарем через API
 */
class InventoryController extends Controller
{
    /**
     * Получить движения инвентаря по SKU
     *
     * @param InventoryMovementsRequest $request
     * @return JsonResponse
     */
    public function movements(InventoryMovementsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $sku = $validated['sku'];

        $movements = InventoryMovement::forSku($sku)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($movements, 200);
    }
}
