<?php

namespace App\Filament\Resources\Memories;

use App\Filament\Resources\Memories\Pages\CreateMemory;
use App\Filament\Resources\Memories\Pages\EditMemory;
use App\Filament\Resources\Memories\Pages\ListMemories;
use App\Filament\Resources\Memories\Schemas\MemoryForm;
use App\Filament\Resources\Memories\Tables\MemoriesTable;
use App\Models\Memory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MemoryResource extends Resource
{
    protected static ?string $model = Memory::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Knowledge Base';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    public static function form(Schema $schema): Schema
    {
        return MemoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MemoriesTable::configure($table)
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Memories\RelationManagers\AuditLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemories::route('/'),
            'create' => CreateMemory::route('/create'),
            'edit' => EditMemory::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\Memories\Widgets\MemoryStatsOverview::class,
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
