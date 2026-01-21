<?php

declare(strict_types=1);

namespace App\Filament\Resources\Memories\Pages;

use App\Filament\Resources\Memories\MemoryResource;
use App\Filament\Resources\Memories\Widgets\MemoryStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemories extends ListRecords
{
    protected static string $resource = MemoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MemoryStatsOverview::class,
        ];
    }
}
