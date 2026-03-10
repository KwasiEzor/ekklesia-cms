<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "Sunday Service" */
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('events', 'slug')->where('tenant_id', tenant('id')),
            ],
            /** @example "2024-05-12T10:00:00Z" */
            'start_at' => ['required', 'date'],
            /** @example "2024-05-12T12:00:00Z" */
            'end_at' => ['nullable', 'date', 'after:start_at'],
            /** @example "Main Sanctuary" */
            'location' => ['nullable', 'string', 'max:255'],
            /** @example "Join us for our weekly Sunday service." */
            'description' => ['nullable', 'string'],
            /** @example "https://example.com/image.jpg" */
            'image' => ['nullable', 'url', 'max:2048'],
            /** @example "https://example.com/register" */
            'registration_url' => ['nullable', 'url', 'max:2048'],
            /** @example 500 */
            'capacity' => ['nullable', 'integer', 'min:1'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
