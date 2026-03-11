<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Campaign
 */
class CampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'fund' => new FundResource($this->whenLoaded('fund')),
            'goal_amount' => $this->goal_amount,
            'currency' => $this->currency,
            'raised_amount' => $this->raised_amount,
            'progress_percent' => $this->progress_percent,
            'donor_count' => $this->donor_count,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->status,
            'is_overdue' => $this->is_overdue,
            'image' => $this->image,
            'custom_fields' => $this->custom_fields ?? (object) [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
