<?php

namespace Database\Seeders;

use App\Models\Repository;
use App\Services\MemoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MemorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(MemoryService $service): void
    {
        $orgId = Str::uuid()->toString();

        // Create Demo Repository
        $repo = Repository::create([
            'organization_id' => $orgId,
            'organization_id' => $organizationId,
            'name' => 'Demo Repository',
            'slug' => 'demo-repo',
            'description' => 'A repository for demonstration purposes.',
            'is_active' => true,
        ]);

        // Seed System Constraints (Global)
        $service->write([
            'id' => Str::uuid()->toString(),
            'organization' => $organizationId, // new key 'organization'
            'repository' => null, // new key 'repository'
            'user' => null, // new key 'user'
            'scope_type' => 'system',
            'memory_type' => 'system_constraint',
            'created_by_type' => 'system',
            'status' => 'locked',
            'current_content' => 'System Constraint: Adhere to ethical AI guidelines.',
        ], 'system', 'system');

        // Seed Default Repo Memory
        $service->write([
            'id' => Str::uuid()->toString(),
            'organization' => $organizationId,
            'repository' => $repo->id,
            'user' => null,
            'scope_type' => 'repository',
            'memory_type' => 'business_rule',
            'created_by_type' => 'human',
            'status' => 'active',
            'current_content' => 'Repo Rule: All commits must be signed.',
        ], 'admin', 'human');

        // 3. User Preference (Repository Scope - just generic example)
        $service->write([
            'organization_id' => $organizationId,
            'repository_id' => $repo->id,
            'scope_type' => 'user',
            'memory_type' => 'preference',
            'created_by_type' => 'human',
            'status' => 'draft',
            'current_content' => 'Prefer short, concise responses.',
        ], 'user-123', 'human');
    }
}
