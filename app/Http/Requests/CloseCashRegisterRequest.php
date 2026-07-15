<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseCashRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cash_register_id' => 'required|integer|exists:cash_registers,id',
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ];
    }
}
