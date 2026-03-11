<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDevotionalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "La force dans la faiblesse" */
            'title' => ['required', 'string', 'max:255'],
            /** @example 1 */
            'series_id' => [
                'nullable',
                'integer',
                Rule::exists('devotional_series', 'id')->where('tenant_id', tenant('id')),
            ],
            /** @example "2 Corinthiens 12:9" */
            'verse_reference' => ['required', 'string', 'max:255'],
            /** @example "Ma grâce te suffit..." */
            'verse_text' => ['nullable', 'string'],
            /** @example "Réflexion sur le verset..." */
            'reflection' => ['required', 'string'],
            /** @example "Seigneur, aide-moi à..." */
            'prayer_point' => ['nullable', 'string'],
            /** @example "Aujourd'hui, je vais..." */
            'application' => ['nullable', 'string'],
            /** @example "2026-03-15" */
            'scheduled_date' => ['required', 'date'],
            /** @example "draft" */
            'status' => ['nullable', 'string', Rule::in(['draft', 'scheduled', 'published'])],
            /** @example "Pasteur Jean" */
            'author' => ['nullable', 'string', 'max:255'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
