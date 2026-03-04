<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'provider' => ['sometimes', 'string', 'in:cinetpay,stripe'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'member_id' => ['nullable', 'integer', Rule::exists('members', 'id')->where('tenant_id', tenant('id'))],
            'campus_id' => ['nullable', 'integer', Rule::exists('campuses', 'id')->where('tenant_id', tenant('id'))],
            'campaign_id' => ['nullable', 'string', 'max:255'],
            'return_url' => ['nullable', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
