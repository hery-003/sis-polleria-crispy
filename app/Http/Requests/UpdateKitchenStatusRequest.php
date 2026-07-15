<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKitchenStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,cooking,ready,completed,cancelled',
            'cancellation_reason' => 'required_if:status,cancelled|string|min:3',
        ];
    }
}
