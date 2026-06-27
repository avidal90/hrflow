<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
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
            'department_id' => Department::factory()->state(fn (array $attributes) => [
                'tenant_id' => $attributes['tenant_id'],
            ]),
            'employee_code' => fake()->unique()->bothify('EMP-####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'employment_status' => 'active',
            'job_title' => fake()->jobTitle(),
        ];
    }
}
