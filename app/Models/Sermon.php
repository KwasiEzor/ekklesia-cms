<?php

namespace App\Models;

use App\Concerns\HasSoftVersioning;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Sermon extends Model
{
    use BelongsToTenant, HasFactory, HasSoftVersioning;

    protected $fillable = [
        'title',
        'slug',
        'speaker',
        'date',
        'duration',
        'audio_url',
        'video_url',
        'transcript',
        'series_id',
        'tags',
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
            'duration' => 'integer',
            'tags' => 'array',
            'custom_fields' => 'array',
            'previous_version' => 'array',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(SermonSeries::class, 'series_id');
    }

    public function getFormattedDurationAttribute(): ?string
    {
        if ($this->duration === null) {
            return null;
        }

        $hours = intdiv($this->duration, 3600);
        $minutes = intdiv($this->duration % 3600, 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
