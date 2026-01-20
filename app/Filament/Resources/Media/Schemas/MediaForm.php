<?php

namespace App\Filament\Resources\Media\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        FileUpload::make('attachment')
                            ->label('File')
                            ->disk('public')
                            ->directory('uploads')
                            ->storeFileNamesIn('original_filename')
                            ->visibility('public')
                            ->required(),
                        Hidden::make('original_filename'),
                    ]),
            ]);
    }
}
