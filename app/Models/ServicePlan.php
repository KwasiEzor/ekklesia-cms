<?php

namespace App\Models;

use App\Concerns\HasCampusScope;
use App\Concerns\HasSoftVersioning;
use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ServicePlan extends Model
{
    use BelongsToTenant, HasCampusScope, HasFactory, HasSlug, HasSoftVersioning, LogsActivityWithTenant;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->extraScope(fn ($builder) => $builder->where('tenant_id', $this->tenant_id));
    }

    protected $fillable = [
        'tenant_id',
        'campus_id',
        'service_type_id',
        'event_id',
        'title',
        'slug',
        'service_date',
        'start_time',
        'theme',
        'notes',
        'roles',
        'status',
        'custom_fields',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'roles' => 'array',
            'custom_fields' => 'array',
            'previous_version' => 'array',
        ];
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class)->orderBy('sort_order');
    }

    public function getTotalDurationAttribute(): int
    {
        return $this->items()->sum('duration_minutes');
    }
}
