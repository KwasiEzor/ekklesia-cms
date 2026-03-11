<?php

namespace App\Models;

use App\Concerns\HasSoftVersioning;
use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Campaign extends Model
{
    use BelongsToTenant, HasFactory, HasSlug, HasSoftVersioning, LogsActivityWithTenant;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->extraScope(fn ($builder) => $builder->where('tenant_id', $this->tenant_id));
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'fund_id',
        'goal_amount',
        'currency',
        'start_date',
        'end_date',
        'status',
        'image',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'goal_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'custom_fields' => 'array',
            'previous_version' => 'array',
        ];
    }

    public function fund(): BelongsTo
    {
        return $this->belongsTo(Fund::class);
    }

    public function givingRecords(): HasMany
    {
        return $this->hasMany(GivingRecord::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where(function (Builder $q): void {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function getRaisedAmountAttribute(): float
    {
        return (float) $this->givingRecords()->sum('amount');
    }

    public function getProgressPercentAttribute(): ?float
    {
        if (! $this->goal_amount || $this->goal_amount <= 0) {
            return null;
        }

        return round(($this->raised_amount / (float) $this->goal_amount) * 100, 1);
    }

    public function getDonorCountAttribute(): int
    {
        return $this->givingRecords()->distinct('member_id')->count('member_id');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->end_date !== null
            && $this->end_date->isPast()
            && $this->status === 'active';
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }
}
