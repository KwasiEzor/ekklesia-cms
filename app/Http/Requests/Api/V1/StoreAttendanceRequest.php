<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example 1 */
            'member_id' => ['required', 'integer', 'exists:members,id'],
            /** @example 1 */
            'service_type_id' => ['nullable', 'integer', 'exists:service_types,id'],
            /** @example null */
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            /** @example null */
            'cell_group_id' => ['nullable', 'integer', 'exists:cell_groups,id'],
            /** @example null */
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
            /** @example "2026-03-09" */
            'date' => ['required', 'date'],
            /** @example "09:15:00" */
            'checked_in_at' => ['nullable', 'date_format:H:i:s'],
            /** @example "12:00:00" */
            'checked_out_at' => ['nullable', 'date_format:H:i:s'],
            /** @example "manual" */
            'check_in_method' => ['nullable', 'string', 'in:manual,qr_code,self_check_in'],
            /** @example false */
            'is_first_time' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
