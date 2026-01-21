<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\MemoryAccessLog;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MemoryUsageStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -3;

    protected function getChartData(?string $type = null): array
    {
        $now = now();
        $start = $now->copy()->subDays(30);

        $query = MemoryAccessLog::query()
            ->whereBetween('created_at', [$start, $now]);

        if ($type === 'search') {
            $query->where('action', 'search');
        } elseif ($type === 'write') {
            $query->whereIn('action', ['create', 'update', 'write']);
        }

        $data = $query->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $chart = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $chart[] = $data[$date] ?? 0;
        }

        return $chart;
    }

    protected function getCount(?string $type = null): int
    {
        $query = MemoryAccessLog::query()
            ->where('created_at', '>=', now()->subDays(30));

        if ($type === 'search') {
            $query->where('action', 'search');
        } elseif ($type === 'write') {
            $query->whereIn('action', ['create', 'update', 'write']);
        }

        return $query->count();
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Requests (30d)', $this->getCount())
                ->description('All memory interactions')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($this->getChartData())
                ->color('success'),
            Stat::make('Total Searches (30d)', $this->getCount('search'))
                ->description('Search queries processed')
                ->color('info')
                ->chart($this->getChartData('search')),
            Stat::make('Write Operations (30d)', $this->getCount('write'))
                ->description('Memories created or updated')
                ->color('warning')
                ->chart($this->getChartData('write')),
        ];
    }
}
