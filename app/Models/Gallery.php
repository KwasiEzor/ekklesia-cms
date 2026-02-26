<?php

namespace App\Models;

use App\Concerns\HasSoftVersioning;
use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Gallery extends Model implements HasMedia
{
    use BelongsToTenant, HasFactory, HasSlug, HasSoftVersioning, InteractsWithMedia, LogsActivityWithTenant;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->extraScope(fn ($builder) => $builder->where('tenant_id', $this->tenant_id));
    }

    protected $fillable = [
        'title',
        'slug',
        'description',
        'galleryable_type',
        'galleryable_id',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'previous_version' => 'array',
        ];
    }

    public function galleryable(): MorphTo
    {
        return $this->morphTo();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('photos');

        $this->addMediaConversion('medium')
            ->width(800)
            ->height(600)
            ->sharpen(5)
            ->performOnCollections('photos');
    }

    public function getPhotosAttribute(): array
    {
        return $this->getMedia('photos')->map(fn (Media $media) => [
            'id' => $media->id,
            'url' => $media->getUrl(),
            'thumb' => $media->getUrl('thumb'),
            'medium' => $media->getUrl('medium'),
            'name' => $media->name,
            'file_name' => $media->file_name,
            'size' => $media->size,
            'order' => $media->order_column,
        ])->toArray();
    }

    public function getPhotoCountAttribute(): int
    {
        return $this->getMedia('photos')->count();
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('photos', 'medium') ?: null;
    }
}
