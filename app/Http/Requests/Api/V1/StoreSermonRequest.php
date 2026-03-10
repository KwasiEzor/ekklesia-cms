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
            /** @example "The Power of Faith" */
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sermons', 'slug')->where('tenant_id', tenant('id')),
            ],
            /** @example "Pastor John Doe" */
            'speaker' => ['required', 'string', 'max:255'],
            /** @example "2024-05-12" */
            'date' => ['required', 'date'],
            /** @example 3600 */
            'duration' => ['nullable', 'integer', 'min:0'],
            /** @example "https://example.com/audio.mp3" */
            'audio_url' => ['nullable', 'url', 'max:2048'],
            /** @example "https://youtube.com/watch?v=123" */
            'video_url' => ['nullable', 'url', 'max:2048'],
            /** @example "Welcome to today's service..." */
            'transcript' => ['nullable', 'string'],
            /** @example 1 */
            'series_id' => ['nullable', 'integer', 'exists:sermon_series,id'],
            /** @example ["faith", "healing"] */
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
