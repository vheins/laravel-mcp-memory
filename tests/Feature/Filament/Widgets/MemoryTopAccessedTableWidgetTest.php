<?php

use App\Filament\Widgets\MemoryTopAccessedTableWidget;
use App\Models\Memory;
use Livewire\Livewire;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can render memory top accessed widget as recent memories', function (): void {
    Memory::factory()->count(3)->create();

    Livewire::test(MemoryTopAccessedTableWidget::class)
        ->assertCanSeeTableRecords(Memory::all());
});
