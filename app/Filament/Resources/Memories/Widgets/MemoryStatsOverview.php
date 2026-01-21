<?php

declare(strict_types=1);

namespace App\Filament\Resources\Memories\Widgets;

use App\Models\Memory;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MemoryStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Memories', Memory::query()->count()),
            Stat::make('Unverified Memories', Memory::query()->where('status', 'draft')->count())
                ->description('Memories pending verification')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            Stat::make('Locked Memories', Memory::query()->where('status', 'locked')->count())
                ->color('success'),
        ];
    }
}
