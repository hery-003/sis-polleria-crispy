<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'number' => 'required|integer|unique:mesas,number,'.$this->route('mesa')->id,
            'capacity' => 'nullable|integer|min:1|max:50',
            'is_active' => 'boolean',
            'reserved_at' => 'nullable|date',
        ];
    }
}
