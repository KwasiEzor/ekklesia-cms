<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGivingRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => [
                'nullable',
                'integer',
                Rule::exists('members', 'id')->where('tenant_id', tenant('id')),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'date' => ['required', 'date'],
            'method' => ['required', 'string', Rule::in(['mobile_money', 'cash', 'bank_transfer', 'card'])],
            'reference' => ['nullable', 'string', 'max:255'],
            'campaign_id' => ['nullable', 'string', 'max:255'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
