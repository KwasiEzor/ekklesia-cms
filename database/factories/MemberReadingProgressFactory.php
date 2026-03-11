<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\MemberReadingProgress;
use App\Models\ReadingPlan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberReadingProgressFactory extends Factory
{
    protected $model = MemberReadingProgress::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'member_id' => Member::factory(),
            'reading_plan_id' => ReadingPlan::factory(),
            'reading_plan_day_id' => null,
            'completed_at' => null,
            'current_streak' => 0,
            'longest_streak' => 0,
            'started_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'completed_at' => now(),
        ]);
    }

    public function withStreak(int $current, ?int $longest = null): static
    {
        return $this->state(fn (): array => [
            'current_streak' => $current,
            'longest_streak' => $longest ?? $current,
        ]);
    }
}
