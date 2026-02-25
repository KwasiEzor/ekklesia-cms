<?php

namespace App\Models;

use App\Concerns\HasSoftVersioning;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Event extends Model
{
    use BelongsToTenant, HasFactory, HasSoftVersioning;

    protected $fillable = [
        'title',
        'slug',
        'start_at',
        'end_at',
        'location',
        'description',
        'image',
        'registration_url',
        'capacity',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'capacity' => 'integer',
            'custom_fields' => 'array',
            'previous_version' => 'array',
        ];
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_at->isFuture();
    }

    public function getIsPastAttribute(): bool
    {
        $end = $this->end_at ?? $this->start_at;

        return $end->isPast();
    }
}
