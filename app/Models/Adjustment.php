<?php

namespace App\Models;

use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Adjustment extends Model
{
    use BelongsToTenant, HasFactory, LogsActivityWithTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'adjustable_type',
        'adjustable_id',
        'type',
        'amount_before',
        'amount_after',
        'reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount_before' => 'decimal:2',
            'amount_after' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adjustable(): MorphTo
    {
        return $this->morphTo();
    }
}
