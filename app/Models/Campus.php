<?php

namespace App\Models;

use App\Concerns\HasSoftVersioning;
use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Campus extends Model
{
    use BelongsToTenant, HasFactory, HasSlug, HasSoftVersioning, LogsActivityWithTenant;

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
        'address',
        'city',
        'country',
        'phone',
        'email',
        'pastor_name',
        'capacity',
        'is_main',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_main' => 'boolean',
            'custom_fields' => 'array',
            'previous_version' => 'array',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function cellGroups(): HasMany
    {
        return $this->hasMany(CellGroup::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function sermons(): HasMany
    {
        return $this->hasMany(Sermon::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function givingRecords(): HasMany
    {
        return $this->hasMany(GivingRecord::class);
    }
}
