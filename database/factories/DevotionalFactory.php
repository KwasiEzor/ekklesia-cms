<?php

namespace Database\Factories;

use App\Models\Devotional;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Devotional>
 */
class DevotionalFactory extends Factory
{
    protected $model = Devotional::class;

    public function definition(): array
    {
        $verses = [
            'Jean 3:16' => 'Car Dieu a tant aimé le monde qu\'il a donné son Fils unique...',
            'Psaumes 23:1' => 'L\'Éternel est mon berger : je ne manquerai de rien.',
            'Philippiens 4:13' => 'Je puis tout par celui qui me fortifie.',
            'Romains 8:28' => 'Toutes choses concourent au bien de ceux qui aiment Dieu.',
            'Jérémie 29:11' => 'Car je connais les projets que j\'ai formés sur vous...',
            'Proverbes 3:5-6' => 'Confie-toi en l\'Éternel de tout ton cœur...',
            'Ésaïe 41:10' => 'Ne crains rien, car je suis avec toi...',
            'Matthieu 6:33' => 'Cherchez premièrement le royaume et la justice de Dieu...',
        ];

        $reference = $this->faker->randomElement(array_keys($verses));

        return [
            'tenant_id' => Tenant::factory(),
            'series_id' => null,
            'title' => $this->faker->sentence(4),
            'verse_reference' => $reference,
            'verse_text' => $verses[$reference],
            'reflection' => $this->faker->paragraphs(2, true),
            'prayer_point' => $this->faker->optional(0.8)->paragraph(),
            'application' => $this->faker->optional(0.7)->paragraph(),
            'scheduled_date' => $this->faker->dateTimeBetween('-1 week', '+2 weeks'),
            'status' => 'draft',
            'published_at' => null,
            'author' => $this->faker->optional(0.6)->name(),
            'custom_fields' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => 'scheduled',
            'scheduled_date' => $this->faker->dateTimeBetween('+1 day', '+2 weeks'),
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (): array => [
            'scheduled_date' => now()->toDateString(),
        ]);
    }
}
