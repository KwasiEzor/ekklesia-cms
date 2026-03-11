<?php

namespace App\Models;

use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class BulkMessage extends Model
{
    use BelongsToTenant, HasFactory, LogsActivityWithTenant;

    protected $fillable = [
        'title',
        'body',
        'channel',
        'target_type',
        'target_id',
        'status',
        'scheduled_at',
        'sent_at',
        'recipient_count',
        'sent_count',
        'failed_count',
        'created_by',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'recipient_count' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
            'custom_fields' => 'array',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    public function scopeSending(Builder $query): Builder
    {
        return $query->where('status', 'sending');
    }

    public function scopeReadyToSend(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }

    public function markAsSending(): void
    {
        $this->update(['status' => 'sending']);
    }

    public function markAsSent(int $sentCount, int $failedCount): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    /**
     * Get the target members for this bulk message.
     */
    public function getRecipients(): \Illuminate\Database\Eloquent\Collection
    {
        $query = Member::query();

        return match ($this->target_type) {
            'all' => $query->whereNotNull('phone')->get(),
            'cell_group' => $query->where('cell_group_id', $this->target_id)->whereNotNull('phone')->get(),
            'campus' => $query->where('campus_id', $this->target_id)->whereNotNull('phone')->get(),
            'status' => $query->where('status', $this->target_id)->whereNotNull('phone')->get(),
            default => new \Illuminate\Database\Eloquent\Collection,
        };
    }
}
