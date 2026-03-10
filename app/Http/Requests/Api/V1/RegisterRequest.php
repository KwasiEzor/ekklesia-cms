<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "Jane Doe" */
            'name' => ['required', 'string', 'max:255'],
            /** @example "jane@example.com" */
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where('tenant_id', tenant('id')),
            ],
            /** @example "SecurePassword123!" */
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            /** @example "MacBook Pro" */
            'device_name' => ['required', 'string', 'max:255'],
        ];
    }
}
