<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Activity extends SpatieActivity
{
    use BelongsToTenant;

    public function scopeTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
