<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PrayerCommitment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'prayer_request_id',
        'member_id',
        'prayed_at',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'prayed_at' => 'datetime',
        ];
    }

    public function prayerRequest(): BelongsTo
    {
        return $this->belongsTo(PrayerRequest::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
