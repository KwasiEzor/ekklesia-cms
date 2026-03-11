<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ServiceType
 */
class ServiceTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'day_of_week' => $this->day_of_week,
            'default_start_time' => $this->default_start_time,
            'expected_duration_minutes' => $this->expected_duration_minutes,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'campus_id' => $this->campus_id,
            'custom_fields' => $this->custom_fields ?? (object) [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
