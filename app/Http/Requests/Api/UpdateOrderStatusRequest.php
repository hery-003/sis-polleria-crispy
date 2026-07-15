<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,cooking,ready,completed,cancelled',
            'reason' => 'required_if:status,cancelled|string|min:3',
        ];
    }
}
