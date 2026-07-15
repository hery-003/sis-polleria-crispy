<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CashMovementApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'register_id' => 'required|exists:cash_registers,id',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
        ];
    }
}
