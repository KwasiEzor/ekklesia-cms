<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTestimonyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "Dieu m'a guéri" */
            'title' => ['sometimes', 'string', 'max:255'],
            /** @example "Mon témoignage de guérison..." */
            'content' => ['sometimes', 'string'],
            /** @example "healing" */
            'category' => ['sometimes', 'string', Rule::in(['healing', 'provision', 'deliverance', 'conversion', 'family_restoration', 'other'])],
            /** @example "approved" */
            'status' => ['sometimes', 'string', Rule::in(['submitted', 'approved', 'rejected', 'featured'])],
            /** @example false */
            'is_anonymous' => ['nullable', 'boolean'],
            /** @example true */
            'is_featured' => ['nullable', 'boolean'],
            /** @example "Contenu incomplet" */
            'rejection_reason' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
