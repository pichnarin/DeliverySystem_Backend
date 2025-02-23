<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
            'order_number' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'note' => ['nullable', 'string', 'max:255'],
            'delivery_fee' => ['required', 'numeric'],
            'tax' => ['required', 'numeric'],
            'discount' => ['required', 'numeric'],
            'payment_method' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'status' => ['required', 'string', 'max:255'],
            'estimated_delivery_time' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
            'customer_id' => ['required', 'numeric', 'exists:users,id'],
            'driver_id' => ['nullable', 'numeric', 'exists:users,id'],
            'addresse_id' => ['required', 'numeric', 'exists:addresses,id'],
        ];
    }
}
