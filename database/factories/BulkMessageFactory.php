<?php

namespace Database\Factories;

use App\Models\BulkMessage;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class BulkMessageFactory extends Factory
{
    protected $model = BulkMessage::class;

    public function definition(): array
    {
        $messages = [
            'Rappel : culte du dimanche à 9h. Venez nombreux !',
            'Réunion de prière ce mercredi à 18h. Soyez bénis.',
            'Séminaire de formation des leaders samedi prochain.',
            'Joyeux Noël à toute la communauté !',
            'Inscription ouverte pour le camp de jeunes.',
            'Veillée de prière ce vendredi de 22h à 5h.',
        ];

        return [
            'tenant_id' => Tenant::factory(),
            'title' => $this->faker->sentence(4),
            'body' => $this->faker->randomElement($messages),
            'channel' => 'sms',
            'target_type' => 'all',
            'target_id' => null,
            'status' => 'draft',
            'scheduled_at' => null,
            'sent_at' => null,
            'recipient_count' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'created_by' => null,
            'custom_fields' => null,
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => 'scheduled',
            'scheduled_at' => now()->addHours(2),
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => 'sent',
            'sent_at' => now()->subHour(),
            'recipient_count' => 50,
            'sent_count' => 48,
            'failed_count' => 2,
        ]);
    }

    public function sending(): static
    {
        return $this->state(fn (): array => [
            'status' => 'sending',
        ]);
    }

    public function forCellGroup(int $cellGroupId): static
    {
        return $this->state(fn (): array => [
            'target_type' => 'cell_group',
            'target_id' => (string) $cellGroupId,
        ]);
    }

    public function forCampus(int $campusId): static
    {
        return $this->state(fn (): array => [
            'target_type' => 'campus',
            'target_id' => (string) $campusId,
        ]);
    }
}
