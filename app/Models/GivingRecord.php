<?php

namespace App\Models;

use App\Concerns\HasSoftVersioning;
use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class GivingRecord extends Model
{
    use BelongsToTenant, HasFactory, HasSoftVersioning, LogsActivityWithTenant;

    protected $fillable = [
        'member_id',
        'amount',
        'currency',
        'date',
        'method',
        'reference',
        'campaign_id',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
            'custom_fields' => 'array',
            'previous_version' => 'array',
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
        return number_format((float) $this->amount, 2, ',', ' ') . ' ' . $this->currency;
    }
}
