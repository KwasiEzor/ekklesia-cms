<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ServiceItem extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'service_plan_id',
        'title',
        'description',
        'duration_minutes',
        'type',
        'roles',
        'sort_order',
        'custom_fields',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'roles' => 'array',
            'sort_order' => 'integer',
            'custom_fields' => 'array',
        ];
    }

    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class);
    }
}
