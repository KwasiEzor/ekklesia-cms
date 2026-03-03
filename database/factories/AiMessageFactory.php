<?php

namespace Database\Factories;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiMessage>
 */
class AiMessageFactory extends Factory
{
    protected $model = AiMessage::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'ai_conversation_id' => AiConversation::factory(),
            'role' => $this->faker->randomElement(['user', 'assistant']),
            'content' => $this->faker->paragraph(),
            'tokens_input' => $this->faker->numberBetween(50, 500),
            'tokens_output' => $this->faker->numberBetween(100, 1000),
            'model' => 'claude-sonnet-4-6',
            'metadata' => null,
        ];
    }

    public function user(): static
    {
        return $this->state(['role' => 'user', 'tokens_input' => null, 'tokens_output' => null, 'model' => null]);
    }

    public function assistant(): static
    {
        return $this->state(['role' => 'assistant']);
    }
}
