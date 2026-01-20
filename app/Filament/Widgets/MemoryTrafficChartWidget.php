<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class MemoryTrafficChartWidget extends ChartWidget
{
    protected static ?int $sort = -2;

    protected ?string $heading = 'Memory Traffic Chart Widget';

    protected function getData(): array
    {
        $data = \App\Models\MemoryAccessLog::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Memory Traffic',
                    'data' => $data->map(fn ($row) => $row->count),
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn ($row) => $row->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
