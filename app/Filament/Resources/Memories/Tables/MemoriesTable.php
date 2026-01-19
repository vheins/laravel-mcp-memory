<?php

namespace App\Filament\Resources\Memories\Tables;

use App\Models\Memory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MemoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('current_content')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('repositoryRel.name')
                    ->label('Repository')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('userRel.name')
                    ->label('User')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Global/System'),
                TextColumn::make('memory_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'system_constraint' => 'danger',
                        'business_rule' => 'warning',
                        'decision_log' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('scope_type')
                    ->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'locked' => 'danger',
                        'deprecated' => 'gray',
                        default => 'info',
                    }),
            ])
            ->filters([
                \Filament\Tables\Filters\TrashedFilter::make(),
                \Filament\Tables\Filters\SelectFilter::make('repository')
                    ->relationship('repositoryRel', 'name')
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
                    ->visible(fn (Memory $record) => $record->status !== 'verified' && $record->status !== 'locked')
                    ->action(fn (Memory $record) => $record->update(['status' => 'verified'])),
                \Filament\Actions\Action::make('lock')
                    ->label('Lock')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Memory $record) => $record->status !== 'locked')
                    ->action(fn (Memory $record) => $record->update(['status' => 'locked'])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
