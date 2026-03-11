<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\GivingRecord
 */
class GivingRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'member_name' => $this->whenLoaded('member', fn () => $this->member?->full_name),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'formatted_amount' => $this->formatted_amount,
            'date' => $this->date->toDateString(),
            'method' => $this->method,
            'reference' => $this->reference,
            'fund' => new FundResource($this->whenLoaded('fund')),
            'campaign' => new CampaignResource($this->whenLoaded('campaign')),
            'is_anonymous' => $this->is_anonymous,
            'custom_fields' => $this->custom_fields ?? (object) [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
