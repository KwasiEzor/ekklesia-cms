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
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('announcements', 'slug')->where('tenant_id', tenant('id')),
            ],
            'body' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:published_at'],
            'pinned' => ['nullable', 'boolean'],
            'target_group' => ['nullable', 'string', 'max:255'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
