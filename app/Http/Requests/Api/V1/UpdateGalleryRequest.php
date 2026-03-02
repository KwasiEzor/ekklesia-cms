<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Event;
use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'galleryable_type' => ['nullable', 'string', Rule::in([Event::class, Member::class])],
            'galleryable_id' => ['nullable', 'integer'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
