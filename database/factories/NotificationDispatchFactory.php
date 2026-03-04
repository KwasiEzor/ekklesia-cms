<?php

namespace Database\Factories;

use App\Models\NotificationDispatch;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationDispatchFactory extends Factory
{
    protected $model = NotificationDispatch::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'channel' => $this->faker->randomElement(['email', 'sms', 'whatsapp', 'telegram']),
            'type' => $this->faker->randomElement(['welcome', 'giving_receipt', 'event_reminder', 'announcement', 'birthday']),
            'status' => 'pending',
            'recipient' => $this->faker->safeEmail(),
            'subject' => $this->faker->sentence(4),
            'body' => $this->faker->paragraph(),
            'metadata' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => 'Connection timeout',
        ]);
    }

    public function sms(): static
    {
        return $this->state(fn (): array => [
            'channel' => 'sms',
            'recipient' => $this->faker->phoneNumber(),
            'subject' => null,
        ]);
    }

    public function email(): static
    {
        return $this->state(fn (): array => [
            'channel' => 'email',
            'recipient' => $this->faker->safeEmail(),
        ]);
    }
}
