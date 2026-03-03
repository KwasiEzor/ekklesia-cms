<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, HasFactory;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    public function setSetting(string $key, mixed $value): void
    {
        $data = $this->data ?? [];
        data_set($data, $key, $value);
        $this->data = $data;
        $this->save();
    }
}
