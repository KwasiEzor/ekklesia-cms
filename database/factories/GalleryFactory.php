<?php

namespace Database\Factories;

use App\Models\Gallery;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class GalleryFactory extends Factory
{
    protected $model = Gallery::class;

    public function definition(): array
    {
        $titles = [
            'Culte du dimanche',
            'Baptêmes',
            'Chorale',
            'Retraite spirituelle',
            'Fête de Noël',
            'Pâques',
            'Camp de jeunes',
            'Journée des femmes',
            'Conférence annuelle',
            'Inauguration',
        ];

        return [
            'tenant_id' => Tenant::factory(),
            'title' => $this->faker->randomElement($titles) . ' ' . $this->faker->year(),
            'description' => $this->faker->boolean(70) ? $this->faker->sentence() : null,
        ];
    }
}
