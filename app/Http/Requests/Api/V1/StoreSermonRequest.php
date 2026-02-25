<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSermonRequest extends FormRequest
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
                Rule::unique('sermons', 'slug')->where('tenant_id', tenant('id')),
            ],
            'speaker' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'audio_url' => ['nullable', 'url', 'max:2048'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'transcript' => ['nullable', 'string'],
            'series_id' => ['nullable', 'integer', 'exists:sermon_series,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
