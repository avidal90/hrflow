<?php

namespace Database\Factories;

use App\Enums\DocumentFolder;
use App\Models\Document;
use App\Models\Tenant;
use App\Models\User;
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
            'user_id' => User::factory()->state(fn (array $attributes) => [
                'tenant_id' => $attributes['tenant_id'],
            ]),
            'uploaded_by_user_id' => User::factory()->state(fn (array $attributes) => [
                'tenant_id' => $attributes['tenant_id'],
            ]),
            'folder' => fake()->randomElement(DocumentFolder::cases())->value,
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'disk' => Document::STORAGE_DISK,
            'original_filename' => fake()->word().'.pdf',
            'file_path' => fn (array $attributes): string => Document::buildStoragePath(
                (string) $attributes['tenant_id'],
                (string) $attributes['user_id'],
                (string) ($attributes['folder'] ?? DocumentFolder::Other->value),
                (string) $attributes['original_filename'],
            ),
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(10000, 3000000),
            'uploaded_at' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s'),
            'is_visible_to_employee' => fake()->boolean(),
        ];
    }
}
