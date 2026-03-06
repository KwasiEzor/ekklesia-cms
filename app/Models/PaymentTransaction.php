<?php

namespace App\Models;

use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PaymentTransaction extends Model
{
    use BelongsToTenant, HasFactory, LogsActivityWithTenant;

    protected string $logName = 'financial';

    protected $fillable = [
        'uuid',
        'member_id',
        'giving_record_id',
        'campus_id',
        'amount',
        'currency',
        'provider',
        'provider_reference',
        'status',
        'payment_method',
        'phone_number',
        'campaign_id',
        'provider_metadata',
        'paid_at',
        'failed_at',
        'failure_reason',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
        'phone_number',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'provider_metadata' => 'array',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $transaction): void {
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) Str::uuid();
            }
        });

        static::updating(function ($record): void {
            if ($record->status !== 'pending' && $record->isDirty(['amount', 'currency', 'member_id', 'provider'])) {
                throw new \Exception('Payment transactions are immutable once processed.');
            }
        });

        static::deleting(function ($record): void {
            throw new \Exception('Payment transactions are immutable and cannot be deleted.');
        });
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function givingRecord(): BelongsTo
    {
        return $this->belongsTo(GivingRecord::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function markAsCompleted(?string $providerReference = null): void
    {
        $this->update([
            'status' => 'completed',
            'paid_at' => now(),
            'provider_reference' => $providerReference ?? $this->provider_reference,
        ]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsPendingAttribute(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format((float) $this->amount, 2, ',', ' ').' '.$this->currency;
    }
}
