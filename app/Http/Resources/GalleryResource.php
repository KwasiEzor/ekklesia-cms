<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'galleryable_type' => $this->galleryable_type ? class_basename($this->galleryable_type) : null,
            'galleryable_id' => $this->galleryable_id,
            'cover_url' => $this->cover_url,
            'photo_count' => $this->photo_count,
            'photos' => $this->photos,
            'custom_fields' => $this->custom_fields ?? (object) [],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
