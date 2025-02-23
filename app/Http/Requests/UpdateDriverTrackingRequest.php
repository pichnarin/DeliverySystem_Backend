<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverTrackingRequest extends FormRequest
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
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            'status' => ['required', 'string', 'max:255'],

            'order_id' => ['required', 'numeric', 'exists:orders,id'],
            'driver_id' => ['required', 'numeric', 'exists:users,id'],
            'address_id' => ['required', 'numeric', 'exists:addresses,id'],
        ];
    }
}
