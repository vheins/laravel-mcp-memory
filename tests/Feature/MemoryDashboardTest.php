<?php

use App\Filament\Widgets\MemoryUserUsageChartWidget;
use App\Filament\Widgets\MemoryTopAccessedTableWidget;
use App\Filament\Widgets\MemoryUsageStatsWidget;
use App\Models\Memory;
use App\Models\MemoryAccessLog;
use App\Services\MemoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('memory service logs access on read', function (): void {
    $memory = Memory::factory()->create(['title' => 'Test Memory']);
    $service = app(MemoryService::class);

    $service->read($memory->id, 'user-1', 'user');

    expect(MemoryAccessLog::query()->count())->toBe(1);
    $log = MemoryAccessLog::query()->first();
    expect($log->action)->toBe('read');
    expect($log->resource_id)->toBe($memory->id);
    expect($log->actor_id)->toBe('user-1');
});

test('memory service logs access on search', function (): void {
    $service = app(MemoryService::class);

    $service->search('repo-1', 'query');

    expect(MemoryAccessLog::query()->count())->toBe(1);
    $log = MemoryAccessLog::query()->first();
    expect($log->action)->toBe('search');
    expect($log->actor_id)->toBe('user-1');
    expect($log->metadata['query'])->toBe('query');
});

test('dashboard stats widget calculates correctly', function (): void {
    // Create some logs
    MemoryAccessLog::query()->create(['action' => 'read', 'created_at' => now()]);
    MemoryAccessLog::query()->create(['action' => 'search', 'created_at' => now()]);
    MemoryAccessLog::query()->create(['action' => 'search', 'created_at' => now()]);
    MemoryAccessLog::query()->create(['action' => 'create', 'created_at' => now()]);

    Livewire::test(MemoryUsageStatsWidget::class)
        ->assertSee('Total Requests (30d)')
        ->assertSee('4')
        ->assertSee('Total Searches (30d)')
        ->assertSee('2')
        ->assertSee('Write Operations (30d)')
        ->assertSee('1');
});

test('user usage widget displays top users', function (): void {
    MemoryAccessLog::query()->create(['actor_id' => 'alice', 'action' => 'read', 'created_at' => now()]);
    MemoryAccessLog::query()->create(['actor_id' => 'alice', 'action' => 'read', 'created_at' => now()]);
    MemoryAccessLog::query()->create(['actor_id' => 'bob', 'action' => 'read', 'created_at' => now()]);

    Livewire::test(MemoryUserUsageChartWidget::class)
        ->assertSee('alice') // alice has 2
        ->assertSee('bob');  // bob has 1
});

test('top accessed memories widget displays popular memories', function (): void {
    $memoryA = Memory::factory()->create(['title' => 'Popular Memory']);
    $memoryB = Memory::factory()->create(['title' => 'Unpopular Memory']);

    // Access Memory A twice
    MemoryAccessLog::query()->create([
        'actor_id' => 'user',
        'action' => 'read',
        'resource_id' => $memoryA->id,
        'created_at' => now(),
    ]);
    MemoryAccessLog::query()->create([
        'actor_id' => 'user',
        'action' => 'read',
        'resource_id' => $memoryA->id,
        'created_at' => now(),
    ]);

    // Access Memory B once
    MemoryAccessLog::query()->create([
        'actor_id' => 'user',
        'action' => 'read',
        'resource_id' => $memoryB->id,
        'created_at' => now(),
    ]);

    Livewire::test(MemoryTopAccessedTableWidget::class)
        ->assertSee('Popular Memory')
        ->assertSee('2') // Count
        ->assertSee('Unpopular Memory')
        ->assertSee('1');
});
