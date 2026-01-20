<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Memory>
 */
class MemoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization' => 'test-org',
            'repository' => 'test-repo',
            'scope_type' => 'organization',
            'memory_type' => 'fact',
            'title' => $this->faker->sentence(),
            'current_content' => $this->faker->paragraph(),
            'status' => 'draft',
            'importance' => 1,
            'created_by_type' => 'human',
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
