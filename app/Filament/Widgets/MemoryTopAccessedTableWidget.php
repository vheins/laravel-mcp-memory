<?php

namespace App\Filament\Widgets;

use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class MemoryTopAccessedTableWidget extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\Memory::query()
                    ->withCount('accessLogs')
                    ->whereHas('accessLogs', fn ($q) => $q->where('created_at', '>=', now()->subDays(30)))
                    ->orderByDesc('access_logs_count')
                    ->limit(10)
            )
            ->heading('Top Accessed Memories')
            ->description('Most frequently accessed memories in the last 30 days.')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('title')
                    ->label('Memory Title')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('access_logs_count')
                    ->label('Access Count (30d)')
                    ->numeric()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
