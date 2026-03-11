<?php

namespace Database\Factories;

use App\Models\ServiceType;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceType>
 */
class ServiceTypeFactory extends Factory
{
    protected $model = ServiceType::class;

    public function definition(): array
    {
        $services = [
            ['name' => 'Culte du Dimanche Matin', 'day' => 'sunday', 'time' => '09:00'],
            ['name' => 'Culte du Dimanche Soir', 'day' => 'sunday', 'time' => '17:00'],
            ['name' => 'Prière du Mercredi', 'day' => 'wednesday', 'time' => '18:00'],
            ['name' => 'Veillée de Prière', 'day' => 'friday', 'time' => '22:00'],
            ['name' => 'Étude Biblique', 'day' => 'thursday', 'time' => '18:30'],
            ['name' => 'Culte de Jeunes', 'day' => 'saturday', 'time' => '15:00'],
        ];

        $service = $this->faker->randomElement($services);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $service['name'],
            'day_of_week' => $service['day'],
            'default_start_time' => $service['time'],
            'expected_duration_minutes' => $this->faker->randomElement([60, 90, 120, 180]),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'custom_fields' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
