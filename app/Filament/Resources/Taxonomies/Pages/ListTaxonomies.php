<?php

declare(strict_types=1);

namespace App\Filament\Resources\Taxonomies\Pages;

use App\Filament\Resources\Taxonomies\TaxonomyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaxonomies extends ListRecords
{
    protected static string $resource = TaxonomyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
