<?php

namespace Database\Factories;

use App\Models\SermonSeries;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SermonSeriesFactory extends Factory
{
    protected $model = SermonSeries::class;

    public function definition(): array
    {
        $title = $this->faker->words(3, true);

        return [
            'tenant_id' => Tenant::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
