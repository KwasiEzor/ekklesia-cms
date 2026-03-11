<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_days' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:7'],
            'is_active' => ['nullable', 'boolean'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
