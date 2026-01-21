<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MemoryScope;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use App\Models\Memory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Memory>
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
            'title' => fake()->sentence(),
            'current_content' => fake()->paragraph(),
            'status' => MemoryStatus::Draft,
            'importance' => 1,
            'created_by_type' => 'human',
            'user_id' => User::factory(),
        ];
    }
}
