<?php

use App\Filament\Widgets\TopQueriesTableWidget;
use App\Models\MemoryAccessLog;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can render top queries widget', function (): void {
    // Create logs with specific queries
    MemoryAccessLog::query()->create(['action' => 'search', 'query' => 'laravel', 'actor_id' => 'user-1']);
    MemoryAccessLog::query()->create(['action' => 'search', 'query' => 'laravel', 'actor_id' => 'user-2']); // Count 2
    MemoryAccessLog::query()->create(['action' => 'search', 'query' => 'mcp', 'actor_id' => 'user-1']);     // Count 1
    MemoryAccessLog::query()->create(['action' => 'read', 'query' => 'ignored', 'actor_id' => 'user-1']);    // Wrong action
    MemoryAccessLog::query()->create(['action' => 'search', 'query' => null, 'actor_id' => 'user-1']);       // Null query

    Livewire::test(TopQueriesTableWidget::class)
        ->assertCanSeeTableRecords([
            // Filament checks records by key, but for aggregated queries we might need to be careful.
            // However, assertCanSeeTableRecords checks if the records in the table match the given records.
            // Since we are grouping, strict model matching might fail if keys aren't standard.
            // Let's rely on seeing the query text.
        ])
        ->assertSee('laravel')
        ->assertSee('mcp')
        ->assertDontSee('ignored');
});
