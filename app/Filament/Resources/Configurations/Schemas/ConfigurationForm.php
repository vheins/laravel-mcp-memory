<?php

declare(strict_types=1);

namespace App\Filament\Resources\Configurations\Schemas;

use App\Models\Configuration;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ConfigurationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (?Configuration $record) => $record?->is_system)
                            ->maxLength(255),
                        TextInput::make('group')
                            ->required()
                            ->maxLength(255)
                            ->default('general'),
                        Select::make('type')
                            ->options([
                                'string' => 'String',
                                'boolean' => 'Boolean',
                                'number' => 'Number',
                                'json' => 'JSON',
                            ])
                            ->required()
                            ->default('string')
                            ->live(),
                        Toggle::make('is_public')
                            ->label('Publicly Available')
                            ->default(false),
                        Toggle::make('is_system')
                            ->label('System Config')
                            ->helperText('Prevent deletion/key editing')
                            ->default(false)
                            ->disabled(),
                    ])->columns(2),

                Section::make('Value')
                    ->schema([
                        Toggle::make('value')
                            ->label('Enabled')
                            ->visible(fn (Get $get): bool => $get('type') === 'boolean'),
                        TextInput::make('value')
                            ->numeric()
                            ->visible(fn (Get $get): bool => $get('type') === 'number'),
                        Textarea::make('value')
                            ->rows(5)
                            ->visible(fn (Get $get): bool => \in_array($get('type'), ['string', 'json', null]))
                            ->json(fn (Get $get): bool => $get('type') === 'json'),
                    ]),
            ]);
    }
}
