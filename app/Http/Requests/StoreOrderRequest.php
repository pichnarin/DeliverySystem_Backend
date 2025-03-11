<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'numeric', 'exists:users,id'],
            'address_id' => ['required', 'numeric', 'exists:addresses,id'],
            'cart_items' => ['required', 'array'],
            'cart_items.*.food_id' => ['required', 'numeric', 'exists:food,id'],
            'cart_items.*.name' => ['required', 'string'],
            'cart_items.*.quantity' => ['required', 'integer', 'min:1'],
            'cart_items.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
