<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,'.$this->route('user')->id,
            'role' => 'required|in:admin,cashier,waiter',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ];
    }
}
