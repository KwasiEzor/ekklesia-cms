<?php

namespace App\Services\Billing;

use App\Models\Campus;
use App\Models\Member;
use App\Models\PlanLimit;
use App\Models\Tenant;

class PlanLimitsEnforcer
{
    public function getPlanLimits(Tenant $tenant): ?PlanLimit
    {
        return $tenant->planLimits();
    }

    public function check(Tenant $tenant, string $feature): bool
    {
        $plan = $this->getPlanLimits($tenant);

        if (! $plan instanceof \App\Models\PlanLimit) {
            return false;
        }

        return match ($feature) {
            'payments' => $plan->has_payments,
            'sms' => $plan->has_sms,
            'whatsapp' => $plan->has_whatsapp,
            'ai' => $plan->has_ai,
            default => false,
        };
    }

    public function memberLimitReached(Tenant $tenant): bool
    {
        $plan = $this->getPlanLimits($tenant);

        if (! $plan || $plan->isUnlimited('max_members')) {
            return false;
        }

        return Member::count() >= $plan->max_members;
    }

    public function campusLimitReached(Tenant $tenant): bool
    {
        $plan = $this->getPlanLimits($tenant);

        if (! $plan || $plan->isUnlimited('max_campuses')) {
            return false;
        }

        return Campus::count() >= $plan->max_campuses;
    }

    public function storageLimitReached(Tenant $tenant): bool
    {
        $plan = $this->getPlanLimits($tenant);

        if (! $plan || $plan->isUnlimited('max_storage_mb')) {
            return false;
        }

        $usedMb = $this->getStorageUsageMb($tenant);

        return $usedMb >= $plan->max_storage_mb;
    }

    public function getStorageUsageMb(Tenant $tenant): float
    {
        $bytes = \Spatie\MediaLibrary\MediaCollections\Models\Media::sum('size');

        return round($bytes / (1024 * 1024), 2);
    }

    public function getUsageSummary(Tenant $tenant): array
    {
        $plan = $this->getPlanLimits($tenant);

        if (! $plan instanceof \App\Models\PlanLimit) {
            return [];
        }

        return [
            'members' => [
                'current' => Member::count(),
                'limit' => $plan->max_members,
                'unlimited' => $plan->isUnlimited('max_members'),
            ],
            'campuses' => [
                'current' => Campus::count(),
                'limit' => $plan->max_campuses,
                'unlimited' => $plan->isUnlimited('max_campuses'),
            ],
            'storage_mb' => [
                'current' => $this->getStorageUsageMb($tenant),
                'limit' => $plan->max_storage_mb,
                'unlimited' => $plan->isUnlimited('max_storage_mb'),
            ],
        ];
    }
}
