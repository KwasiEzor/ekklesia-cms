<?php

namespace App\Models;

use App\Concerns\HasCampusScope;
use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Attendance extends Model
{
    use BelongsToTenant, HasCampusScope, HasFactory, LogsActivityWithTenant;

    protected $fillable = [
        'member_id',
        'service_type_id',
        'event_id',
        'cell_group_id',
        'campus_id',
        'date',
        'checked_in_at',
        'checked_out_at',
        'check_in_method',
        'is_first_time',
        'notes',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_first_time' => 'boolean',
            'custom_fields' => 'array',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function cellGroup(): BelongsTo
    {
        return $this->belongsTo(CellGroup::class);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForServiceType(Builder $query, int $serviceTypeId): Builder
    {
        return $query->where('service_type_id', $serviceTypeId);
    }

    public function scopeFirstTimers(Builder $query): Builder
    {
        return $query->where('is_first_time', true);
    }

    public function scopeForDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeForMember(Builder $query, int $memberId): Builder
    {
        return $query->where('member_id', $memberId);
    }
}
