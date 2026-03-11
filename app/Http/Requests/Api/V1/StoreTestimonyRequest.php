<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTestimonyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "Dieu m'a guéri" */
            'title' => ['required', 'string', 'max:255'],
            /** @example 1 */
            'member_id' => [
                'nullable',
                'integer',
                Rule::exists('members', 'id')->where('tenant_id', tenant('id')),
            ],
            /** @example "Mon témoignage de guérison..." */
            'content' => ['required', 'string'],
            /** @example "healing" */
            'category' => ['required', 'string', Rule::in(['healing', 'provision', 'deliverance', 'conversion', 'family_restoration', 'other'])],
            /** @example false */
            'is_anonymous' => ['nullable', 'boolean'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
