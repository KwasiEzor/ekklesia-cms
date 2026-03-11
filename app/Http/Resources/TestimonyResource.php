<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Testimony
 */
class TestimonyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'category' => $this->category,
            'status' => $this->status,
            'is_anonymous' => $this->is_anonymous,
            'is_featured' => $this->is_featured,
            'member' => $this->when(! $this->is_anonymous, fn () => [
                'id' => $this->whenLoaded('member', fn () => $this->member?->id),
                'name' => $this->whenLoaded('member', fn () => $this->member?->full_name),
            ]),
            'reactions' => $this->reactions ?? (object) [],
            'reaction_count' => $this->reaction_count,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'custom_fields' => $this->custom_fields ?? (object) [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
