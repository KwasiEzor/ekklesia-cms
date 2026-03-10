<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "About Us" */
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->where('tenant_id', tenant('id')),
            ],
            /** @example [{"type": "text", "data": {"content": "Welcome to our church"}}] */
            'content_blocks' => ['nullable', 'array'],
            'content_blocks.*.type' => ['required_with:content_blocks', 'string'],
            'content_blocks.*.data' => ['required_with:content_blocks', 'array'],
            /** @example "About Our Church - Learn More" */
            'seo_title' => ['nullable', 'string', 'max:255'],
            /** @example "Learn about our church history, mission, and vision." */
            'seo_description' => ['nullable', 'string', 'max:255'],
            /** @example "2024-05-01T00:00:00Z" */
            'published_at' => ['nullable', 'date'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
