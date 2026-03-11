<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /** @example "Construction du temple" */
            'name' => ['required', 'string', 'max:255'],
            /** @example "Campagne pour la construction du nouveau temple" */
            'description' => ['nullable', 'string'],
            /** @example 1 */
            'fund_id' => [
                'nullable',
                'integer',
                Rule::exists('funds', 'id')->where('tenant_id', tenant('id')),
            ],
            /** @example 5000000.00 */
            'goal_amount' => ['nullable', 'numeric', 'min:0'],
            /** @example "XOF" */
            'currency' => ['required', 'string', 'size:3'],
            /** @example "2026-01-01" */
            'start_date' => ['required', 'date'],
            /** @example "2026-12-31" */
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            /** @example "active" */
            'status' => ['nullable', 'string', Rule::in(['draft', 'active', 'completed', 'cancelled'])],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
