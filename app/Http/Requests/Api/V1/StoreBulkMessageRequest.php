<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBulkMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "Rappel culte" */
            'title' => ['required', 'string', 'max:255'],
            /** @example "Rappel : culte du dimanche à 9h. Venez nombreux !" */
            'body' => ['required', 'string', 'max:1600'],
            /** @example "sms" */
            'channel' => ['required', 'string', Rule::in(['sms', 'email', 'whatsapp'])],
            /** @example "all" */
            'target_type' => ['required', 'string', Rule::in(['all', 'cell_group', 'campus', 'status'])],
            'target_id' => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
