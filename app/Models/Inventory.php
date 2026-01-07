<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'sku',
        'available_qty',
        'reserved_qty',
    ];

    /**
     * Резервирование товара на складе
     *
     * Уменьшает доступное количество (available_qty) и увеличивает зарезервированное (reserved_qty).
     * Использует pessimistic locking (lockForUpdate) для предотвращения race conditions
     * при одновременных запросах на резервирование одного и того же товара.
     *
     * Блокировка гарантирует, что между проверкой наличия товара и его резервированием
     * другие процессы не смогут изменить данные, что предотвращает ситуацию,
     * когда несколько заказов одновременно резервируют последние единицы товара.
     *
     * @param int $qty Количество товара для резервирования
     * @return bool true если резервирование успешно, false если недостаточно товара
     */
    public function reserve(int $qty): bool
    {
        return DB::transaction(function () use ($qty) {
            $inventory = self::lockForUpdate()->find($this->id);

            if (!$inventory || $inventory->available_qty < $qty) {
                return false;
            }

            $inventory->available_qty -= $qty;
            $inventory->reserved_qty += $qty;
            return $inventory->save();
        });
    }

    /**
     * Освобождение резерва товара
     *
     * Увеличивает доступное количество (available_qty) и уменьшает зарезервированное (reserved_qty).
     * Использует pessimistic locking для предотвращения race conditions при одновременном
     * освобождении резерва несколькими процессами.
     *
     * @param int $qty Количество товара для освобождения из резерва
     * @return void
     */
    public function release(int $qty): void
    {
        DB::transaction(function () use ($qty) {
            $inventory = self::lockForUpdate()->find($this->id);

            if ($inventory) {
                $inventory->available_qty += $qty;
                $inventory->reserved_qty -= $qty;
                $inventory->save();
            }
        });
    }

    /**
     * Проверка достаточности доступных запасов
     *
     * @param int $qty Требуемое количество товара
     * @return bool true если доступного товара достаточно, false в противном случае
     */
    public function hasEnough(int $qty): bool
    {
        return $this->available_qty >= $qty;
    }

    /**
     * Получение общего количества товара на складе
     *
     * Сумма доступного и зарезервированного количества.
     *
     * @return int Общее количество товара
     */
    public function getTotalQty(): int
    {
        return $this->available_qty + $this->reserved_qty;
    }
}
