<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'title' => $this->faker->randomElement([
                'Accueil',
                'À propos',
                'Nos ministères',
                'Contact',
                'Déclaration de foi',
                'Notre histoire',
                'Leadership',
                'Vie de l\'église',
            ]) . ' ' . $this->faker->unique()->numberBetween(1, 9999),
            'content_blocks' => null,
            'seo_title' => $this->faker->optional(0.5)->sentence(6),
            'seo_description' => $this->faker->optional(0.5)->sentence(12),
            'published_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', '+1 week'),
            'custom_fields' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'published_at' => $this->faker->dateTimeBetween('-1 month', '-1 hour'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'published_at' => null,
        ]);
    }

    public function withBlocks(): static
    {
        return $this->state(fn () => [
            'content_blocks' => [
                [
                    'type' => 'heading',
                    'data' => [
                        'level' => 'h2',
                        'content' => 'Bienvenue à notre église',
                    ],
                ],
                [
                    'type' => 'rich_text',
                    'data' => [
                        'body' => 'Nous sommes une communauté de croyants passionnés par la Parole de Dieu.',
                    ],
                ],
                [
                    'type' => 'quote',
                    'data' => [
                        'text' => 'Car Dieu a tant aimé le monde qu\'il a donné son Fils unique.',
                        'attribution' => 'Jean 3:16',
                    ],
                ],
                [
                    'type' => 'call_to_action',
                    'data' => [
                        'label' => 'Rejoignez-nous',
                        'url' => '/contact',
                        'style' => 'primary',
                    ],
                ],
            ],
        ]);
    }
}
