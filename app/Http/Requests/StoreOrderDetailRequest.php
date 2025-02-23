<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderDetailRequest extends FormRequest
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
            'food_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric'],
            'price' => ['required', 'numeric'],
            'sub_total' => ['required', 'numeric'],

            'order_id' => ['required', 'numeric', 'exists:orders,id'],
            'food_id' => ['required', 'numeric', 'exists:food,id'],
        ];
    }
}
