<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class MemberReadingProgress extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'member_reading_progress';

    protected $fillable = [
        'member_id',
        'reading_plan_id',
        'reading_plan_day_id',
        'completed_at',
        'current_streak',
        'longest_streak',
        'started_at',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'started_at' => 'date',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function readingPlan(): BelongsTo
    {
        return $this->belongsTo(ReadingPlan::class);
    }

    public function readingPlanDay(): BelongsTo
    {
        return $this->belongsTo(ReadingPlanDay::class);
    }

    public function scopeForMember(Builder $query, int $memberId): Builder
    {
        return $query->where('member_id', $memberId);
    }

    public function scopeForPlan(Builder $query, int $planId): Builder
    {
        return $query->where('reading_plan_id', $planId);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeNotCompleted(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }
}
