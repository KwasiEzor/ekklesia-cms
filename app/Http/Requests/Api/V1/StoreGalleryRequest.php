<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Event;
use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "Youth Camp Memories" */
            'title' => ['required', 'string', 'max:255'],
            /** @example "Photos from the 2024 youth camp." */
            'description' => ['nullable', 'string', 'max:1000'],
            /** @example "App\\Models\\Event" */
            'galleryable_type' => ['nullable', 'string', Rule::in([Event::class, Member::class])],
            /** @example 1 */
            'galleryable_id' => ['nullable', 'integer'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
