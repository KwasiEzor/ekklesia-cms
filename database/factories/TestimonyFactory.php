<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Testimony;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Testimony>
 */
class TestimonyFactory extends Factory
{
    protected $model = Testimony::class;

    public function definition(): array
    {
        $categories = ['healing', 'provision', 'deliverance', 'conversion', 'family_restoration', 'other'];
        $titles = [
            'healing' => ['Dieu m\'a guéri d\'une maladie', 'Ma guérison miraculeuse', 'Guérison par la prière'],
            'provision' => ['Dieu a pourvu à mes besoins', 'Un miracle financier', 'La provision divine'],
            'deliverance' => ['Délivré des chaînes', 'La liberté en Christ', 'Délivrance spirituelle'],
            'conversion' => ['Ma rencontre avec Jésus', 'Comment j\'ai trouvé la foi', 'Ma conversion'],
            'family_restoration' => ['Mon foyer restauré', 'Réconciliation familiale', 'Dieu a sauvé mon mariage'],
            'other' => ['Ce que Dieu a fait pour moi', 'Un témoignage de grâce', 'La fidélité de Dieu'],
        ];

        $category = $this->faker->randomElement($categories);

        return [
            'tenant_id' => Tenant::factory(),
            'member_id' => null,
            'title' => $this->faker->randomElement($titles[$category]),
            'content' => $this->faker->paragraphs(3, true),
            'category' => $category,
            'status' => 'submitted',
            'is_anonymous' => false,
            'is_featured' => false,
            'approved_at' => null,
            'approved_by' => null,
            'rejection_reason' => null,
            'reactions' => null,
            'custom_fields' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => 'Pasteur Jean',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'status' => 'rejected',
            'rejection_reason' => 'Contenu nécessite plus de détails.',
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => [
            'status' => 'approved',
            'is_featured' => true,
            'approved_at' => now(),
            'approved_by' => 'Pasteur Jean',
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn (): array => [
            'is_anonymous' => true,
        ]);
    }

    public function withReactions(): static
    {
        return $this->state(fn (): array => [
            'reactions' => [
                'amen' => $this->faker->numberBetween(1, 50),
                'gloire_a_dieu' => $this->faker->numberBetween(1, 30),
                'alleluia' => $this->faker->numberBetween(1, 20),
            ],
        ]);
    }
}
