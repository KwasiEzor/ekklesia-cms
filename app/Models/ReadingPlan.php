<?php

namespace App\Models;

use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ReadingPlan extends Model
{
    use BelongsToTenant, HasFactory, HasSlug, LogsActivityWithTenant;

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
        'duration_days',
        'grace_period_days',
        'is_active',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'grace_period_days' => 'integer',
            'is_active' => 'boolean',
            'custom_fields' => 'array',
        ];
    }

    public function days(): HasMany
    {
        return $this->hasMany(ReadingPlanDay::class)->orderBy('day_number');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MemberReadingProgress::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getSubscriberCountAttribute(): int
    {
        return $this->subscriptions()->distinct('member_id')->count('member_id');
    }
}
