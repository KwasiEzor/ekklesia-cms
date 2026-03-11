<?php

namespace App\Models;

use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PrayerRequest extends Model
{
    use BelongsToTenant, HasFactory, LogsActivityWithTenant;

    protected $fillable = [
        'member_id',
        'cell_group_id',
        'type',
        'visibility',
        'title',
        'content',
        'category',
        'status',
        'is_anonymous',
        'is_featured',
        'answered_at',
        'testimony',
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
            'answered_at' => 'datetime',
            'custom_fields' => 'array',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function cellGroup(): BelongsTo
    {
        return $this->belongsTo(CellGroup::class);
    }

    public function commitments(): HasMany
    {
        return $this->hasMany(PrayerCommitment::class);
    }

    public function getPrayerCountAttribute(): int
    {
        return $this->commitments()->count();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeAnswered(Builder $query): Builder
    {
        return $query->where('status', 'answered');
    }

    public function scopePublicVisibility(Builder $query): Builder
    {
        return $query->where('visibility', 'public');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function markAsAnswered(?string $testimony = null): void
    {
        $this->update([
            'status' => 'answered',
            'answered_at' => now(),
            'testimony' => $testimony,
        ]);
    }
}
