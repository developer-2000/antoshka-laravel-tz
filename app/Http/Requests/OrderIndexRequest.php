<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request для получения списка заказов
 */
class OrderIndexRequest extends FormRequest
{
    /**
     * Определить, авторизован ли пользователь для выполнения этого запроса
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Правила валидации
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:' . implode(',', [
                Order::STATUS_PENDING,
                Order::STATUS_RESERVED,
                Order::STATUS_AWAITING_RESTOCK,
                Order::STATUS_FAILED,
            ])],
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}

