<?php

use App\Models\User;
use App\Services\MemoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('casts actor_id to string in logAccess during search', function () {
    $user = User::factory()->create();
    actingAs($user);

    $service = new MemoryService();

    // logic to ensure we don't get TypeError
    try {
        $results = $service->search('test');
        expect($results)->toBeIterable();
    } catch (\TypeError $e) {
        $this->fail('TypeError thrown: ' . $e->getMessage());
    }
});
