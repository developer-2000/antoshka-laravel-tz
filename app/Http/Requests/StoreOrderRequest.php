<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:1'],
        ];
    }

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
}
