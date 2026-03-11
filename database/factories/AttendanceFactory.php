<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\ServiceType;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'member_id' => Member::factory(),
            'service_type_id' => ServiceType::factory(),
            'event_id' => null,
            'cell_group_id' => null,
            'campus_id' => null,
            'date' => $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'checked_in_at' => $this->faker->optional(0.7)->time('H:i:s'),
            'check_in_method' => $this->faker->randomElement(['manual', 'qr_code', 'self_check_in']),
            'is_first_time' => false,
            'notes' => null,
            'custom_fields' => null,
        ];
    }

    public function firstTime(): static
    {
        return $this->state(fn (): array => [
            'is_first_time' => true,
        ]);
    }

    public function forEvent(): static
    {
        return $this->state(fn (): array => [
            'service_type_id' => null,
            'event_id' => \App\Models\Event::factory(),
        ]);
    }

    public function forCellGroup(): static
    {
        return $this->state(fn (): array => [
            'service_type_id' => null,
            'cell_group_id' => \App\Models\CellGroup::factory(),
        ]);
    }
}
