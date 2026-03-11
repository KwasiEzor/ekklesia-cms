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
            /** @example 1 */
            'member_id' => [
                'nullable',
                'integer',
                Rule::exists('members', 'id')->where('tenant_id', tenant('id')),
            ],
            /** @example 50.00 */
            'amount' => ['required', 'numeric', 'min:0'],
            /** @example "USD" */
            'currency' => ['required', 'string', 'size:3'],
            /** @example "2024-05-12" */
            'date' => ['required', 'date'],
            /** @example "card" */
            'method' => ['required', 'string', Rule::in(['mobile_money', 'cash', 'bank_transfer', 'card'])],
            /** @example "TXN-12345" */
            'reference' => ['nullable', 'string', 'max:255'],
            /** @example 1 */
            'fund_id' => [
                'nullable',
                'integer',
                Rule::exists('funds', 'id')->where('tenant_id', tenant('id')),
            ],
            /** @example 1 */
            'campaign_id' => [
                'nullable',
                'integer',
                Rule::exists('campaigns', 'id')->where('tenant_id', tenant('id')),
            ],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
