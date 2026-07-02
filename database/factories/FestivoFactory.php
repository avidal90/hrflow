<?php

namespace Database\Factories;

use App\Models\Festivo;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Festivo>
 */
class FestivoFactory extends Factory
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
            'date' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
        ];
    }
}
