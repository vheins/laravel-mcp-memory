<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Repository>
 */
class RepositoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'name' => fake()->words(3, true),
            'organization_id' => fake()->uuid(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
