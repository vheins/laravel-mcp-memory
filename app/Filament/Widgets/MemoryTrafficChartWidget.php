<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\MemoryAccessLog;
use Filament\Widgets\ChartWidget;

class MemoryTrafficChartWidget extends ChartWidget
{
    protected ?string $heading = 'Memory Traffic';

    protected static ?int $sort = -2;

    public function getDescription(): ?string
    {
        return 'Total memory interactions over the last 30 days.';
    }

    protected function getData(): array
    {
        $data = MemoryAccessLog::query()->selectRaw('DATE(created_at) as date, count(*) as count')
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
