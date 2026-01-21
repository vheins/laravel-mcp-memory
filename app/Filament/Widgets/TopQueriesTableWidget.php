<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\MemoryAccessLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class TopQueriesTableWidget extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                MemoryAccessLog::query()
                    ->select([
                        'query',
                        DB::raw('count(*) as count'),
                        DB::raw('MAX(created_at) as last_searched_at'),
                        DB::raw('MIN(id) as id'),
                    ])
                    ->where('action', 'search')
                    ->whereNotNull('query')
                    ->where('query', '!=', '')
                    ->groupBy('query')
                    ->orderByDesc('count')
                    ->orderByDesc('last_searched_at')
                    ->limit(10)
            )
            ->heading('Top Search Queries')
            ->description('Most frequent search queries.')
            ->columns([
                TextColumn::make('query')
                    ->label('Search Query')
                    ->searchable(),
                TextColumn::make('count')
                    ->label('Count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_searched_at')
                    ->label('Last Searched')
                    ->dateTime()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
