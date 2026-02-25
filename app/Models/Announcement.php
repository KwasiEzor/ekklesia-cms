<?php

namespace App\Models;

use App\Concerns\HasSoftVersioning;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Announcement extends Model
{
    use BelongsToTenant, HasFactory, HasSoftVersioning;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'published_at',
        'expires_at',
        'pinned',
        'target_group',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'pinned' => 'boolean',
            'custom_fields' => 'array',
            'previous_version' => 'array',
        ];
    }

    public function getIsActiveAttribute(): bool
    {
        if ($this->published_at === null || $this->published_at->isFuture()) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
