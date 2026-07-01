<?php

namespace Database\Factories;

use App\Enums\TimeEntryStatus;
use App\Models\Tenant;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory()->state(fn (array $attributes) => [
                'tenant_id' => $attributes['tenant_id'],
            ]),
            'work_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'check_in_time' => '09:00:00',
            'check_out_time' => '17:00:00',
            'duration_minutes' => 480,
            'status' => TimeEntryStatus::Complete->value,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
