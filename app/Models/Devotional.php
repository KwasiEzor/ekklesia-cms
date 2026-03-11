<?php

namespace App\Models;

use App\Concerns\HasSoftVersioning;
use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Devotional extends Model
{
    use BelongsToTenant, HasFactory, HasSlug, HasSoftVersioning, LogsActivityWithTenant;

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
        'series_id',
        'verse_reference',
        'verse_text',
        'reflection',
        'prayer_point',
        'application',
        'scheduled_date',
        'status',
        'published_at',
        'author',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'published_at' => 'datetime',
            'custom_fields' => 'array',
            'previous_version' => 'array',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(DevotionalSeries::class, 'series_id');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->where('scheduled_date', $date);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('scheduled_date', '>=', now()->toDateString())
            ->whereIn('status', ['scheduled', 'published'])
            ->orderBy('scheduled_date');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->forDate(now()->toDateString());
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }
}
