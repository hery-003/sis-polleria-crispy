<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMetodoPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:metodos_pago,slug,'.$this->route('metodoPago')->id,
            'is_active' => 'boolean',
            'qr_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ];
    }
}
