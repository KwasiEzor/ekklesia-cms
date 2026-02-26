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
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->where('tenant_id', tenant('id')),
            ],
            'content_blocks' => ['nullable', 'array'],
            'content_blocks.*.type' => ['required_with:content_blocks', 'string'],
            'content_blocks.*.data' => ['required_with:content_blocks', 'array'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
