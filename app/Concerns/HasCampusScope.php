<?php

namespace App\Concerns;

use App\Models\Campus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCampusScope
{
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function scopeForCampus(Builder $query, ?int $campusId): Builder
    {
        if ($campusId === null) {
            return $query;
        }

        return $query->where('campus_id', $campusId);
    }
}
