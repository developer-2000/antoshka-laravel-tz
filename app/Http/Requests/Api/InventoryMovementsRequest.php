<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Request для получения движений инвентаря по SKU через API
 */
class InventoryMovementsRequest extends FormRequest
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
            'sku' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Сообщения об ошибках валидации
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'sku.required' => 'SKU не может быть пустым',
            'sku.string' => 'SKU должен быть строкой',
            'sku.max' => 'SKU не может быть длиннее 255 символов',
        ];
    }

    /**
     * Подготовить данные для валидации
     * Добавляем параметр маршрута sku в данные для валидации
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => $this->route('sku'),
        ]);
    }

    /**
     * Обработка неудачной валидации
     *
     * @param Validator $validator
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'error' => $validator->errors()->first('sku', 'SKU не может быть пустым'),
            ], 400)
        );
    }
}

