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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('members', 'email')->where('tenant_id', tenant('id')),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'baptism_date' => ['nullable', 'date'],
            'cell_group_id' => ['nullable', 'integer', 'exists:cell_groups,id'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'visiting', 'transferred'])],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
