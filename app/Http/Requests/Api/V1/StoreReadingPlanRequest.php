<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "Les Psaumes en 30 jours" */
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            /** @example 30 */
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            /** @example 1 */
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:7'],
            'is_active' => ['nullable', 'boolean'],
            'days' => ['nullable', 'array'],
            'days.*.day_number' => ['required_with:days', 'integer', 'min:1'],
            'days.*.title' => ['nullable', 'string', 'max:255'],
            'days.*.passage_reference' => ['required_with:days', 'string', 'max:255'],
            'days.*.passage_text' => ['nullable', 'string'],
            'days.*.reflection' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
