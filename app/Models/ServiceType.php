<?php

namespace App\Models;

use App\Concerns\HasCampusScope;
use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ServiceType extends Model
{
    use BelongsToTenant, HasCampusScope, HasFactory, HasSlug, LogsActivityWithTenant;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->extraScope(fn ($builder) => $builder->where('tenant_id', $this->tenant_id));
    }

    protected $fillable = [
        'name',
        'slug',
        'day_of_week',
        'default_start_time',
        'expected_duration_minutes',
        'is_active',
        'sort_order',
        'campus_id',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'expected_duration_minutes' => 'integer',
            'custom_fields' => 'array',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
