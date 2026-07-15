<?php

namespace App\Http\Requests\Api;

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
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,yape,plin',
            'type' => 'required|in:dine_in,take_out,delivery',
            'mesa_id' => 'nullable|exists:mesas,id',
            'metodo_pago_id' => 'nullable|exists:metodos_pago,id',
            'client_id' => 'nullable|exists:clients,id',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
