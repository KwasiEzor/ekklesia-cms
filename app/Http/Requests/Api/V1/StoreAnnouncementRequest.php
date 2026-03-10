<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "Youth Ministry Meeting" */
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('announcements', 'slug')->where('tenant_id', tenant('id')),
            ],
            /** @example "Join us this Friday for a time of worship and fellowship." */
            'body' => ['nullable', 'string'],
            /** @example "2024-05-01T10:00:00Z" */
            'published_at' => ['nullable', 'date'],
            /** @example "2024-05-31T23:59:59Z" */
            'expires_at' => ['nullable', 'date', 'after:published_at'],
            /** @example true */
            'pinned' => ['nullable', 'boolean'],
            /** @example "youth" */
            'target_group' => ['nullable', 'string', 'max:255'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
