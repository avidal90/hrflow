<?php

namespace Database\Factories;

use App\Enums\LeaveRequestStatus;
use App\Enums\LeaveRequestType;
use App\Models\LeaveRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+2 months');
        $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(1, 7).' days');

        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory()->state(fn (array $attributes) => [
                'tenant_id' => $attributes['tenant_id'],
            ]),
            'request_type' => LeaveRequestType::Vacation->value,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'reason' => fake()->sentence(),
            'status' => LeaveRequestStatus::Pending->value,
            'resolved_by_user_id' => null,
            'resolved_at' => null,
            'manager_comment' => null,
        ];
    }
}
