<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePOSOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,yape,plin',
            'type' => 'required|in:dine_in,take_out,delivery',
            'total' => 'nullable|numeric',
            'metodo_pago_id' => 'nullable|integer|exists:metodos_pago,id',
            'notes' => 'nullable|string',
            'mesa_id' => 'nullable|integer|exists:mesas,id',
            'client_id' => 'nullable|integer|exists:clients,id',
            'auto_pay' => 'nullable|boolean',
            'received_amount' => 'nullable|numeric|min:0',
            'change' => 'nullable|numeric|min:0',
        ];
    }
}
