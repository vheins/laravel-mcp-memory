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
                    ->latest()
                    ->limit(10)
            )
            ->heading('Recent Memories')
            ->description('Most recently created memories.')
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('title')
                    ->label('Memory Title')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
