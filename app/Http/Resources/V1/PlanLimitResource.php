<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanLimitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'plan_slug' => $this->plan_slug,
            'name' => $this->name,
            'price' => $this->price_cents / 100,
            'currency' => $this->currency ?? 'USD',
            'max_members' => $this->max_members,
            'max_campuses' => $this->max_campuses,
            'max_storage_mb' => $this->max_storage_mb,
            'features' => $this->features ?? [],
            'is_purchasable' => ! empty($this->stripe_price_id),
        ];
    }
}
