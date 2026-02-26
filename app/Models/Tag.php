<?php

namespace App\Models;

use Spatie\Tags\Tag as SpatieTag;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Tag extends SpatieTag
{
    use BelongsToTenant;

    protected $hidden = [
        'tenant_id',
    ];
}
