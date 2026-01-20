<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MemoryUsageStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -3;

    protected function getStats(): array
    {
        $now = now();
        $start = $now->copy()->subDays(30);

        $totalRequests = \App\Models\MemoryAccessLog::whereBetween('created_at', [$start, $now])->count();
        $totalSearches = \App\Models\MemoryAccessLog::where('action', 'search')
            ->whereBetween('created_at', [$start, $now])
            ->count();
        $totalWrites = \App\Models\MemoryAccessLog::whereIn('action', ['create', 'update', 'write'])
            ->whereBetween('created_at', [$start, $now])
            ->count();

        return [
            Stat::make('Total Requests (30d)', $totalRequests)
                ->description('All memory interactions')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17]) // Placeholder chart, real one would need more query
                ->color('success'),
            Stat::make('Total Searches (30d)', $totalSearches)
                ->description('Search queries processed')
                ->color('info'),
            Stat::make('Write Operations (30d)', $totalWrites)
                ->description('Memories created or updated')
                ->color('warning'),
        ];
    }
}
