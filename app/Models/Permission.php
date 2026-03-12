<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            if (tenant('id')) {
                $table = $builder->getModel()->getTable();
                $builder->where(function ($query) use ($table): void {
                    $query->where("{$table}.team_id", tenant('id'))
                        ->orWhereNull("{$table}.team_id");
                });
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'team_id');
    }
}
