<?php

declare(strict_types=1);

namespace App\Filament\Resources\Taxonomies;

use App\Filament\Resources\Taxonomies\Pages\CreateTaxonomy;
use App\Filament\Resources\Taxonomies\Pages\EditTaxonomy;
use App\Filament\Resources\Taxonomies\Pages\ListTaxonomies;
use App\Filament\Resources\Taxonomies\RelationManagers\TermsRelationManager;
use App\Filament\Resources\Taxonomies\Schemas\TaxonomyForm;
use App\Filament\Resources\Taxonomies\Tables\TaxonomiesTable;
use App\Models\Taxonomy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class TaxonomyResource extends Resource
{
    protected static ?string $model = Taxonomy::class;

    protected static UnitEnum|string|null $navigationGroup = 'Knowledge Base';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';

    public static function form(Schema $schema): Schema
    {
        return TaxonomyForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaxonomies::route('/'),
            'create' => CreateTaxonomy::route('/create'),
            'edit' => EditTaxonomy::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            TermsRelationManager::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return TaxonomiesTable::configure($table)
            ->defaultSort('created_at', 'desc');
    }
}
