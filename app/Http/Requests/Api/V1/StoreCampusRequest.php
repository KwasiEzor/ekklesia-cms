<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('campuses', 'slug')->where('tenant_id', tenant('id')),
            ],
            /** @example "123 Main St" */
            'address' => ['nullable', 'string'],
            /** @example "London" */
            'city' => ['nullable', 'string', 'max:255'],
            /** @example "UK" */
            'country' => ['nullable', 'string', 'max:255'],
            /** @example "+44 20 7123 4567" */
            'phone' => ['nullable', 'string', 'max:255'],
            /** @example "london@church.com" */
            'email' => ['nullable', 'email', 'max:255'],
            /** @example "John Doe" */
            'pastor_name' => ['nullable', 'string', 'max:255'],
            /** @example 500 */
            'capacity' => ['nullable', 'integer', 'min:1'],
            /** @example true */
            'is_main' => ['nullable', 'boolean'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
