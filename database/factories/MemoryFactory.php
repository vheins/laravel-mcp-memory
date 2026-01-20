<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use App\Enums\MemoryScope;

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
            'scope_type' => MemoryScope::Organization,
            'memory_type' => MemoryType::Fact,
            'title' => $this->faker->sentence(),
            'current_content' => $this->faker->paragraph(),
            'status' => MemoryStatus::Draft,
            'importance' => 1,
            'created_by_type' => 'human',
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
