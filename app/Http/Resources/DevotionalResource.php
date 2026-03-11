<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Devotional
 */
class DevotionalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'series' => new DevotionalSeriesResource($this->whenLoaded('series')),
            'verse_reference' => $this->verse_reference,
            'verse_text' => $this->verse_text,
            'reflection' => $this->reflection,
            'prayer_point' => $this->prayer_point,
            'application' => $this->application,
            'scheduled_date' => $this->scheduled_date->toDateString(),
            'status' => $this->status,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toIso8601String(),
            'author' => $this->author,
            'custom_fields' => $this->custom_fields ?? (object) [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
