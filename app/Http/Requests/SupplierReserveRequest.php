<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Request для создания резервации у поставщика
 */
class SupplierReserveRequest extends FormRequest
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
            'qty' => ['required', 'integer', 'min:1'],
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
            'sku.required' => 'Артикул товара обязателен для заполнения.',
            'sku.string' => 'Артикул товара должен быть строкой.',
            'sku.max' => 'Артикул товара не может быть длиннее 255 символов.',
            'qty.required' => 'Количество товара обязательно для заполнения.',
            'qty.integer' => 'Количество товара должно быть целым числом.',
            'qty.min' => 'Количество товара должно быть не менее 1.',
        ];
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
                'accepted' => false,
                'error' => $validator->errors()->first(),
            ], 422)
        );
    }
}

