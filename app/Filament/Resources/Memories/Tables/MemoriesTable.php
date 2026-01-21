<?php

namespace App\Filament\Resources\Memories\Tables;

use App\Models\Memory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MemoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('current_content')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('repository')
                    ->label('Repository')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('userRel.name')
                    ->label('User')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Global/System'),
                TextColumn::make('memory_type')
                    ->badge(),
                TextColumn::make('scope_type')
                    ->badge(),
                TextColumn::make('importance')
                    ->badge()
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 8 => 'danger',
                        $state >= 5 => 'warning',
                        default => 'info',
                    }),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                \Filament\Tables\Filters\TrashedFilter::make(),
                \Filament\Tables\Filters\SelectFilter::make('repository')
                    ->options(fn () => Memory::query()->whereNotNull('repository')->distinct()->pluck('repository', 'repository'))
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'verified' => 'Verified',
                        'locked' => 'Locked',
                        'deprecated' => 'Deprecated',
                    ]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Memory $record) => $record->status === \App\Enums\MemoryStatus::Draft)
                    ->action(fn (Memory $record) => $record->update(['status' => 'verified'])),
                \Filament\Actions\Action::make('lock')
                    ->label('Lock')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Memory $record) => $record->status !== \App\Enums\MemoryStatus::Locked)
                    ->action(fn (Memory $record) => $record->update(['status' => 'locked'])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->groups([
                \Filament\Tables\Grouping\Group::make('userRel.name')
                    ->label('User'),
                \Filament\Tables\Grouping\Group::make('organization'),
                \Filament\Tables\Grouping\Group::make('memory_type'),
                \Filament\Tables\Grouping\Group::make('scope_type'),
                \Filament\Tables\Grouping\Group::make('status'),
            ]);
    }
}
