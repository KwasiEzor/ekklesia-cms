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
            /** @example 100.50 */
            'amount' => ['required', 'numeric', 'min:1'],
            /** @example "USD" */
            'currency' => ['sometimes', 'string', 'size:3'],
            /** @example "stripe" */
            'provider' => ['sometimes', 'string', 'in:cinetpay,stripe'],
            /** @example "card" */
            'payment_method' => ['nullable', 'string', 'max:50'],
            /** @example "+1234567890" */
            'phone_number' => ['nullable', 'string', 'max:50'],
            /** @example 1 */
            'member_id' => ['nullable', 'integer', Rule::exists('members', 'id')->where('tenant_id', tenant('id'))],
            /** @example 1 */
            'campus_id' => ['nullable', 'integer', Rule::exists('campuses', 'id')->where('tenant_id', tenant('id'))],
            /** @example "building-fund-2024" */
            'campaign_id' => ['nullable', 'string', 'max:255'],
            /** @example "https://example.com/payment/success" */
            'return_url' => ['nullable', 'url', 'max:2048'],
            /** @example "Donation for the building fund" */
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
