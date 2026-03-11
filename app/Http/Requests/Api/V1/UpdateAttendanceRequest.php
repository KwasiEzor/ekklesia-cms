<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['sometimes', 'required', 'integer', 'exists:members,id'],
            'service_type_id' => ['nullable', 'integer', 'exists:service_types,id'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'cell_group_id' => ['nullable', 'integer', 'exists:cell_groups,id'],
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
            'date' => ['sometimes', 'required', 'date'],
            'checked_in_at' => ['nullable', 'date_format:H:i:s'],
            'checked_out_at' => ['nullable', 'date_format:H:i:s'],
            'check_in_method' => ['nullable', 'string', 'in:manual,qr_code,self_check_in'],
            'is_first_time' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
