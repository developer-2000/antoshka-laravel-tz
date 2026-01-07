<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryMovement;

/**
 * Сервис для работы с инвентарем
 */
class InventoryService
{
    /**
     * Получить список инвентаря с фильтрацией и сортировкой
     *
     * @param string|null $search Поисковый запрос по SKU
     * @param string $sortBy Поле для сортировки
     * @param string $sortDir Направление сортировки (asc/desc)
     * @param int $perPage Количество элементов на странице
     * @return array Массив с данными: inventory, sortBy, sortDir
     */
    public function getInventoryList(?string $search, string $sortBy = 'sku', string $sortDir = 'asc', int $perPage = 5): array
    {
        $query = Inventory::query();

        if ($search) {
            $query->where('sku', 'like', '%' . $search . '%');
        }

        $allowedSorts = ['sku', 'available_qty', 'reserved_qty'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        $inventory = $query->paginate($perPage);

        return [
            'inventory' => $inventory,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ];
    }

    /**
     * Получить движения инвентаря по SKU с фильтрацией
     *
     * @param string $sku Артикул товара
     * @param string|null $movementType Тип движения
     * @param string|null $dateFrom Дата начала периода
     * @param string|null $dateTo Дата окончания периода
     * @param int $perPage Количество элементов на странице
     * @return array Массив с данными: movements, inventory
     */
    public function getMovementsBySku(
        string $sku,
        ?string $movementType = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $perPage = 20
    ): array {
        $query = InventoryMovement::where('sku', $sku);

        if ($movementType) {
            $query->where('movement_type', $movementType);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $inventory = Inventory::where('sku', $sku)->first();

        return [
            'movements' => $movements,
            'inventory' => $inventory,
        ];
    }
}

