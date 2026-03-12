<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PrayerRequest
 */
class PrayerRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member' => $this->when(! $this->is_anonymous, fn (): array => [
                'id' => $this->member->id,
                'full_name' => $this->member->full_name,
            ]),
            'is_anonymous' => $this->is_anonymous,
            'type' => $this->type,
            'visibility' => $this->visibility,
            'title' => $this->title,
            'content' => $this->content,
            'category' => $this->category,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'prayer_count' => $this->prayer_count,
            'answered_at' => $this->answered_at?->toIso8601String(),
            'testimony' => $this->testimony,
            'custom_fields' => $this->custom_fields ?? (object) [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
