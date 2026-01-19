<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Repository;
use App\Services\MemoryService;
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
            'name' => 'Demo Repository',
            'slug' => 'demo-repo',
            'description' => 'A repository for demonstration purposes.',
            'is_active' => true,
        ]);

        // 1. System Constraint (System Scope)
        $service->write([
            'organization_id' => $orgId,
            'repository_id' => null,
            'scope_type' => 'system',
            'memory_type' => 'system_constraint',
            'created_by_type' => 'human',
            'status' => 'locked',
            'current_content' => 'AI Agents must ensure regression safety by running tests before finalizing changes.',
        ], 'system-bootstrapper', 'human');

        // 2. Business Rule (Repository Scope)
        $service->write([
            'organization_id' => $orgId,
            'repository_id' => $repo->id,
            'scope_type' => 'repository',
            'memory_type' => 'business_rule',
            'created_by_type' => 'human',
            'status' => 'verified',
            'current_content' => 'All features must follow the TDD workflow.',
        ], 'project-manager', 'human');

        // 3. User Preference (Repository Scope - just generic example)
        $service->write([
            'organization_id' => $orgId,
            'repository_id' => $repo->id,
            'scope_type' => 'user',
            'memory_type' => 'preference',
            'created_by_type' => 'human',
            'status' => 'draft',
            'current_content' => 'Prefer short, concise responses.',
        ], 'user-123', 'human');
    }
}
