<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class MemoryEventsChartWidget extends ChartWidget
{
    protected static ?int $sort = -1;

    protected ?string $heading = 'Memory Events Breakdown';

    public function getDescription(): ?string
    {
        return 'Distribution of event types in the last 30 days.';
    }

    protected function getData(): array
    {
        $data = \App\Models\MemoryAccessLog::selectRaw('action, count(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('action')
            ->orderByDesc('count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Event Types',
                    'data' => $data->map(fn ($row) => $row->count),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1'],
                ],
            ],
            'labels' => $data->map(fn ($row) => $row->action),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
