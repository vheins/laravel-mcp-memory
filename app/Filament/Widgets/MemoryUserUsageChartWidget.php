<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\MemoryAccessLog;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class MemoryUserUsageChartWidget extends ChartWidget
{
    protected ?string $heading = 'User Usage';

    protected static ?int $sort = 0;

    public function getDescription(): ?string
    {
        return 'Top users by interaction count in the last 30 days.';
    }

    protected function getData(): array
    {
        $data = MemoryAccessLog::query()->selectRaw('actor_id, count(*) as count')
            ->whereNotNull('actor_id')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('actor_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Fetch user names for the actor_ids
        $actorIds = $data->pluck('actor_id')->filter()->toArray();
        $users = User::query()->whereIn('id', $actorIds)->pluck('name', 'id');

        return [
            'datasets' => [
                [
                    'label' => 'Top Users',
                    'data' => $data->map(fn ($row) => $row->count),
                    'backgroundColor' => '#8b5cf6', // violet
                ],
            ],
            'labels' => $data->map(fn ($row) => $users[$row->actor_id] ?? $row->actor_id ?? 'Unknown'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
