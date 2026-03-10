<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "user@example.com" */
            'email' => ['required', 'string', 'email'],
            /** @example "password123" */
            'password' => ['required', 'string'],
            /** @example "iPhone 15 Pro" */
            'device_name' => ['required', 'string', 'max:255'],
        ];
    }
}
