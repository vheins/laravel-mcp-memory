<?php

namespace App\Filament\Widgets;

use App\Models\MemoryAccessLog;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopQueriesTableWidget extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                MemoryAccessLog::query()
                    ->select([
                        'query',
                        \Illuminate\Support\Facades\DB::raw('count(*) as count'),
                        \Illuminate\Support\Facades\DB::raw('MAX(created_at) as last_searched_at'),
                        \Illuminate\Support\Facades\DB::raw('MIN(id) as id'),
                    ])
                    ->where('action', 'search')
                    ->whereNotNull('query')
                    ->where('query', '!=', '')
                    ->groupBy('query')
                    ->orderByDesc('count')
                    ->limit(10)
            )
            ->heading('Top Search Queries')
            ->description('Most frequent search queries.')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('query')
                    ->label('Search Query')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('count')
                    ->label('Count')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('last_searched_at')
                    ->label('Last Searched')
                    ->dateTime()
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
