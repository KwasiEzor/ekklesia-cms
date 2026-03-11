<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "Dîmes" */
            'name' => ['sometimes', 'string', 'max:255'],
            /** @example "Description du fonds" */
            'description' => ['nullable', 'string'],
            /** @example "tithe" */
            'type' => ['sometimes', 'string', Rule::in(['tithe', 'offering', 'building', 'missions', 'benevolence', 'other'])],
            /** @example true */
            'is_active' => ['nullable', 'boolean'],
            /** @example 0 */
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
