<?php

namespace App\Models;

use App\Concerns\HasCampusScope;
use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class GivingRecord extends Model
{
    use BelongsToTenant, HasCampusScope, HasFactory, LogsActivityWithTenant;

    protected string $logName = 'financial';

    protected $fillable = [
        'member_id',
        'amount',
        'currency',
        'date',
        'method',
        'reference',
        'campaign_id',
        'campus_id',
        'custom_fields',
    ];

    protected static function booted(): void
    {
        static::updating(function ($record): void {
            if ($record->isDirty(['amount', 'currency', 'date', 'member_id', 'method'])) {
                throw new \Exception('Financial records are immutable. Corrections must be handled via new records.');
            }
        });

        static::deleting(function ($record): void {
            throw new \Exception('Financial records are immutable and cannot be deleted.');
        });
    }

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
            'custom_fields' => 'array',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function getIsAnonymousAttribute(): bool
    {
        return $this->member_id === null;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format((float) $this->amount, 2, ',', ' ').' '.$this->currency;
    }
}
