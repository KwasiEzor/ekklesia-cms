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

class Testimony extends Model
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
        'member_id',
        'content',
        'category',
        'status',
        'is_anonymous',
        'is_featured',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'reactions',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'is_featured' => 'boolean',
            'approved_at' => 'datetime',
            'reactions' => 'array',
            'custom_fields' => 'array',
            'previous_version' => 'array',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'submitted');
    }

    public function scopeOfCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function approve(string $approvedBy): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy,
            'rejection_reason' => null,
        ]);
    }

    public function reject(string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    public function addReaction(string $type): void
    {
        $reactions = $this->reactions ?? [];
        $reactions[$type] = ($reactions[$type] ?? 0) + 1;
        $this->update(['reactions' => $reactions]);
    }

    public function getReactionCountAttribute(): int
    {
        if (! $this->reactions) {
            return 0;
        }

        return array_sum($this->reactions);
    }
}
