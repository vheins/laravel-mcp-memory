<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Memory;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class MemoryTopAccessedTableWidget extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Memory::query()
                    ->latest()
                    ->limit(10)
            )
            ->heading('Recent Memories')
            ->description('Most recently created memories.')
            ->columns([
                TextColumn::make('title')
                    ->label('Memory Title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
