<?php

namespace App\Filament\Resources\Memories\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MemoryStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Total Memories', \App\Models\Memory::count()),
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Unverified Memories', \App\Models\Memory::where('status', 'draft')->count())
                ->description('Memories pending verification')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            \Filament\Widgets\StatsOverviewWidget\Stat::make('Locked Memories', \App\Models\Memory::where('status', 'locked')->count())
                ->color('success'),
        ];
    }
}
