<?php

namespace App\Filament\Resources\Configurations\Tables;

use App\Models\Configuration;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConfigurationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->limit(50),
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'string',
                        'success' => 'boolean',
                        'warning' => 'number',
                        'danger' => 'json',
                    ]),
                TextColumn::make('group')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('is_public')
                    ->boolean(),
                IconColumn::make('is_system')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->options(fn () => Configuration::query()->pluck('group', 'group')->unique()->toArray()),
                SelectFilter::make('type')
                    ->options([
                        'string' => 'String',
                        'boolean' => 'Boolean',
                        'number' => 'Number',
                        'json' => 'JSON',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                     ->hidden(fn (Configuration $record) => $record->is_system),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
