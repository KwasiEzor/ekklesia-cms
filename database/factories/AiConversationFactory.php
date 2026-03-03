<?php

namespace Database\Factories;

use App\Models\AiConversation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiConversation>
 */
class AiConversationFactory extends Factory
{
    protected $model = AiConversation::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'locale' => 'fr',
            'metadata' => null,
        ];
    }
}
