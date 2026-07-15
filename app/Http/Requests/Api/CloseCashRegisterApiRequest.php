<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CloseCashRegisterApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'register_id' => 'required|exists:cash_registers,id',
            'actual_closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
