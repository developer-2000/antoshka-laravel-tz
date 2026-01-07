<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Сервис для эмуляции API поставщика
 */
class SupplierApiService
{
    /**
     * Создать резервацию у поставщика
     *
     * @param string $sku Артикул товара
     * @param int $qty Количество товара
     * @return array Массив с accepted и ref
     */
    public function createReservation(string $sku, int $qty): array
    {
        $ref = $this->generateRef();
        
        // Сохраняем статус резервации
        Cache::put("supplier_status_{$ref}", 'delayed', 3600);
        
        // Сохраняем данные резервации для возможного использования
        Cache::put("supplier_reservation_{$ref}", [
            'sku' => $sku,
            'qty' => $qty,
        ], 3600);

        return [
            'accepted' => true,
            'ref' => $ref,
        ];
    }

    /**
     * Получить статус резервации
     *
     * @param string $ref Референс резервации
     * @return string Статус (ok, fail, delayed)
     */
    public function getStatus(string $ref): string
    {
        $cachedStatus = Cache::get("supplier_status_{$ref}");

        if ($cachedStatus) {
            $status = $cachedStatus;
        } else {
            $statuses = ['ok', 'fail', 'delayed'];
            $status = $statuses[array_rand($statuses)];
            Cache::put("supplier_status_{$ref}", $status, 3600);
        }

        // Случайное изменение статуса delayed на ok (1 из 3 шансов)
        if ($status === 'delayed' && rand(1, 3) === 1) {
            $status = 'ok';
            Cache::put("supplier_status_{$ref}", $status, 3600);
        }

        return $status;
    }

    /**
     * Проверить валидность формата ref
     *
     * @param string $ref Референс резервации
     * @return bool true если формат валиден
     */
    public function validateRefFormat(string $ref): bool
    {
        return preg_match('/^SUP-\d{8}\d{3}$/', $ref) === 1;
    }

    /**
     * Сгенерировать уникальный ref
     *
     * @return string Референс в формате SUP-YYYYMMDDXXX
     */
    private function generateRef(): string
    {
        return 'SUP-' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
}

