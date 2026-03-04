<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use Billable, HasDatabase, HasDomains, HasFactory;

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
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'trial_ends_at',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function currentPlanSlug(): string
    {
        return $this->plan_slug ?? 'free';
    }

    public function planLimits(): ?PlanLimit
    {
        return PlanLimit::where('plan_slug', $this->currentPlanSlug())->first();
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        // VirtualColumn stores non-custom columns in the JSON 'data' column.
        // Access them as direct attributes, not via $this->data.
        return $this->$key ?? $default;
    }

    public function setSetting(string $key, mixed $value): void
    {
        $this->$key = $value;
        $this->save();
    }
}
