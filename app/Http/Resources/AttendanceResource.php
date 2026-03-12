<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Attendance
 */
class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'member' => $this->whenLoaded('member', fn (): array => [
                'id' => $this->member->id,
                'full_name' => $this->member->full_name,
            ]),
            'service_type_id' => $this->service_type_id,
            'service_type' => $this->whenLoaded('serviceType', fn (): array => [
                'id' => $this->serviceType->id,
                'name' => $this->serviceType->name,
            ]),
            'event_id' => $this->event_id,
            'cell_group_id' => $this->cell_group_id,
            'campus_id' => $this->campus_id,
            'date' => $this->date->toDateString(),
            'checked_in_at' => $this->checked_in_at,
            'checked_out_at' => $this->checked_out_at,
            'check_in_method' => $this->check_in_method,
            'is_first_time' => $this->is_first_time,
            'notes' => $this->notes,
            'custom_fields' => $this->custom_fields ?? (object) [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
