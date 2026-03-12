<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'stripe_id' => $this->stripe_id,
            'status' => $this->stripe_status,
            'price_id' => $this->stripe_price,
            'quantity' => $this->quantity,
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'on_trial' => $this->onTrial(),
            'on_grace_period' => $this->onGracePeriod(),
            'active' => $this->active(),
            'cancelled' => $this->canceled(),
        ];
    }
}
