<?php

declare(strict_types=1);

namespace App\Filament\Resources\Memories\Tables;

use App\Enums\MemoryScope;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use App\Models\Memory;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

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
                TrashedFilter::make(),
                SelectFilter::make('repository')
                    ->options(fn () => Memory::query()->whereNotNull('repository')->distinct()->pluck('repository', 'repository'))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'verified' => 'Verified',
                        'locked' => 'Locked',
                        'deprecated' => 'Deprecated',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Memory $record): bool => $record->status === MemoryStatus::Draft)
                    ->action(fn (Memory $record) => $record->update(['status' => 'verified'])),
                Action::make('lock')
                    ->label('Lock')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Memory $record): bool => $record->status !== MemoryStatus::Locked)
                    ->action(fn (Memory $record) => $record->update(['status' => 'locked'])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('status')
                                ->options(MemoryStatus::class)
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) => $records->each->update(['status' => $data['status']]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('updateMemoryType')
                        ->label('Update Memory Type')
                        ->icon('heroicon-o-tag')
                        ->form([
                            Select::make('memory_type')
                                ->options(MemoryType::class)
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) => $records->each->update(['memory_type' => $data['memory_type']]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('updateScopeType')
                        ->label('Update Scope Type')
                        ->icon('heroicon-o-globe-alt')
                        ->form([
                            Select::make('scope_type')
                                ->options(MemoryScope::class)
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) => $records->each->update(['scope_type' => $data['scope_type']]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->groups([
                Group::make('userRel.name')
                    ->label('User'),
                Group::make('organization'),
                Group::make('memory_type'),
                Group::make('scope_type'),
                Group::make('status'),
            ]);
    }
}
