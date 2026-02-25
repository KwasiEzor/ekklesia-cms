<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SermonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'speaker' => $this->speaker,
            'date' => $this->date->toDateString(),
            'duration' => $this->duration,
            'formatted_duration' => $this->formatted_duration,
            'audio_url' => $this->audio_url,
            'video_url' => $this->video_url,
            'transcript' => $this->transcript,
            'series' => $this->whenLoaded('series', fn () => [
                'id' => $this->series->id,
                'title' => $this->series->title,
                'slug' => $this->series->slug,
            ]),
            'tags' => $this->tags ?? [],
            'custom_fields' => $this->custom_fields ?? (object) [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
