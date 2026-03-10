<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "John" */
            'first_name' => ['required', 'string', 'max:255'],
            /** @example "Doe" */
            'last_name' => ['required', 'string', 'max:255'],
            /** @example "john.doe@example.com" */
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('members', 'email')->where('tenant_id', tenant('id')),
            ],
            /** @example "+1234567890" */
            'phone' => ['nullable', 'string', 'max:255'],
            /** @example "2020-01-01" */
            'baptism_date' => ['nullable', 'date'],
            /** @example 1 */
            'cell_group_id' => ['nullable', 'integer', 'exists:cell_groups,id'],
            /** @example "active" */
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'visiting', 'transferred'])],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
