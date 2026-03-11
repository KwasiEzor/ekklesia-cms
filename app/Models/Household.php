<?php

namespace App\Models;

use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Household extends Model
{
    use BelongsToTenant, HasFactory, HasSlug, LogsActivityWithTenant;

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
        'phone',
        'custom_fields',
        'tenant_id',
    ];

    protected $hidden = [
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function head(): ?Member
    {
        return $this->members()->where('family_role', 'head')->first();
    }

    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }
}
