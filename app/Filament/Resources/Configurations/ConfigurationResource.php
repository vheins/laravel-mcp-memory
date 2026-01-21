<?php

declare(strict_types=1);

namespace App\Filament\Resources\Configurations;

use App\Filament\Resources\Configurations\Pages\CreateConfiguration;
use App\Filament\Resources\Configurations\Pages\EditConfiguration;
use App\Filament\Resources\Configurations\Pages\ListConfigurations;
use App\Filament\Resources\Configurations\Schemas\ConfigurationForm;
use App\Filament\Resources\Configurations\Tables\ConfigurationsTable;
use App\Models\Configuration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ConfigurationResource extends Resource
{
    protected static ?string $model = Configuration::class;

    protected static UnitEnum|string|null $navigationGroup = 'System';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function form(Schema $schema): Schema
    {
        return ConfigurationForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConfigurations::route('/'),
            'create' => CreateConfiguration::route('/create'),
            'edit' => EditConfiguration::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function table(Table $table): Table
    {
        return ConfigurationsTable::configure($table)
            ->defaultSort('created_at', 'desc');
    }
}
