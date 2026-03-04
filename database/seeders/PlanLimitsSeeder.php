<?php

namespace Database\Seeders;

use App\Models\PlanLimit;
use Illuminate\Database\Seeder;

class PlanLimitsSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'plan_slug' => 'free',
                'name' => 'Free',
                'max_members' => 50,
                'max_storage_mb' => 500,
                'max_campuses' => 1,
                'has_payments' => false,
                'has_sms' => false,
                'has_whatsapp' => false,
                'has_ai' => false,
                'ai_tier' => null,
                'price_cents' => 0,
                'currency' => 'USD',
            ],
            [
                'plan_slug' => 'basic',
                'name' => 'Basic',
                'max_members' => 500,
                'max_storage_mb' => 5120,
                'max_campuses' => 1,
                'has_payments' => false,
                'has_sms' => true,
                'has_whatsapp' => false,
                'has_ai' => true,
                'ai_tier' => 'basic',
                'price_cents' => 1500,
                'currency' => 'USD',
            ],
            [
                'plan_slug' => 'premium',
                'name' => 'Premium',
                'max_members' => 5000,
                'max_storage_mb' => 25600,
                'max_campuses' => 5,
                'has_payments' => true,
                'has_sms' => true,
                'has_whatsapp' => true,
                'has_ai' => true,
                'ai_tier' => 'full',
                'price_cents' => 3000,
                'currency' => 'USD',
            ],
            [
                'plan_slug' => 'enterprise',
                'name' => 'Enterprise',
                'max_members' => 0, // unlimited
                'max_storage_mb' => 102400,
                'max_campuses' => 0, // unlimited
                'has_payments' => true,
                'has_sms' => true,
                'has_whatsapp' => true,
                'has_ai' => true,
                'ai_tier' => 'full',
                'price_cents' => 0, // custom pricing
                'currency' => 'USD',
            ],
        ];

        foreach ($plans as $plan) {
            PlanLimit::updateOrCreate(
                ['plan_slug' => $plan['plan_slug']],
                $plan,
            );
        }
    }
}
