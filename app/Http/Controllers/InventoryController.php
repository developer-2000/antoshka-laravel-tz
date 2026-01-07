<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryIndexRequest;
use App\Http\Requests\InventoryMovementsRequest;
use App\Services\InventoryService;
use Illuminate\View\View;

/**
 * Контроллер для работы с инвентарем
 */
class InventoryController extends Controller
{
    /**
     * @param InventoryService $inventoryService
     */
    public function __construct(
        private InventoryService $inventoryService
    ) {
    }

    /**
     * Отобразить список инвентаря
     *
     * @param InventoryIndexRequest $request
     * @return View
     */
    public function index(InventoryIndexRequest $request): View
    {
        $validated = $request->validated();
        
        $data = $this->inventoryService->getInventoryList(
            $validated['search'] ?? null,
            $validated['sort_by'] ?? 'sku',
            $validated['sort_dir'] ?? 'asc'
        );

        return view('inventory.index', $data);
    }

    /**
     * Отобразить движения инвентаря по SKU
     *
     * @param InventoryMovementsRequest $request
     * @param string $sku Артикул товара
     * @return View
     */
    public function movements(InventoryMovementsRequest $request, string $sku): View
    {
        $validated = $request->validated();
        
        $data = $this->inventoryService->getMovementsBySku(
            $sku,
            $validated['movement_type'] ?? null,
            $validated['date_from'] ?? null,
            $validated['date_to'] ?? null
        );

        return view('inventory.movements', array_merge($data, ['sku' => $sku]));
    }
}
