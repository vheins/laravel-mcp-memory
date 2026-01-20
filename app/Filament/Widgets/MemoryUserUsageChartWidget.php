<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class MemoryUserUsageChartWidget extends ChartWidget
{
    protected static ?int $sort = 0;

    protected function getData(): array
    {
        $data = \App\Models\MemoryAccessLog::selectRaw('actor_id, count(*) as count')
            ->whereNotNull('actor_id')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('actor_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Top Users',
                    'data' => $data->map(fn ($row) => $row->count),
                    'backgroundColor' => '#8b5cf6', // violet
                ],
            ],
            'labels' => $data->map(fn ($row) => $row->actor_id ?? 'Unknown'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
