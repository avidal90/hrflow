<?php

namespace Database\Factories;

use App\Enums\DocumentCategory;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
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
            'employee_id' => Employee::factory()->state(fn (array $attributes) => [
                'tenant_id' => $attributes['tenant_id'],
            ]),
            'category' => fake()->randomElement(DocumentCategory::cases())->value,
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'file_path' => 'documents/'.fake()->uuid().'.pdf',
            'mime_type' => fake()->randomElement(['application/pdf', 'image/png']),
            'file_size' => fake()->numberBetween(10000, 3000000),
            'uploaded_at' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s'),
            'is_visible_to_employee' => fake()->boolean(),
        ];
    }
}
