<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ReadingPlan
 */
class ReadingPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'duration_days' => $this->duration_days,
            'grace_period_days' => $this->grace_period_days,
            'is_active' => $this->is_active,
            'subscriber_count' => $this->whenCounted('subscriptions', $this->subscriber_count),
            'days' => ReadingPlanDayResource::collection($this->whenLoaded('days')),
            'custom_fields' => $this->custom_fields ?? (object) [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
