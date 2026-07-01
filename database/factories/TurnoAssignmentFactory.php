<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Turno;
use App\Models\TurnoAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TurnoAssignment>
 */
class TurnoAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'turno_id' => Turno::factory()->state(fn (array $attributes): array => [
                'tenant_id' => $attributes['tenant_id'],
            ]),
            'user_id' => User::factory()->state(fn (array $attributes): array => [
                'tenant_id' => $attributes['tenant_id'],
            ]),
            'valid_from' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'valid_until' => fake()->boolean(50)
                ? fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d')
                : null,
        ];
    }
}
