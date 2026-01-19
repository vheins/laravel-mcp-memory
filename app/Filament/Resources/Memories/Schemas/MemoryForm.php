<?php

namespace App\Filament\Resources\Memories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MemoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Context')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('repository')
                            ->label('Repository')
                            ->placeholder('e.g. owner/repo')
                            ->helperText('Free text repository identifier (e.g. vheins/laravel-mcp-memory)'),
                        \Filament\Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('userRel', 'name')
                            ->searchable()
                            ->preload(),
                        \Filament\Schemas\Components\Grid::make(3)
                            ->schema([
                                \Filament\Forms\Components\Select::make('scope_type')
                                    ->options([
                                        'system' => 'System',
                                        'organization' => 'Organization',
                                        'repository' => 'Repository',
                                        'user' => 'User',
                                    ])
                                    ->required(),
                                \Filament\Forms\Components\Select::make('memory_type')
                                    ->options([
                                        'business_rule' => 'Business Rule',
                                        'decision_log' => 'Decision Log',
                                        'preference' => 'Preference',
                                        'system_constraint' => 'System Constraint',
                                        'documentation_ref' => 'Documentation Ref',
                                        'tech_stack' => 'Tech Stack',
                                    ])
                                    ->required(),
                                \Filament\Forms\Components\Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'verified' => 'Verified',
                                        'locked' => 'Locked',
                                        'deprecated' => 'Deprecated',
                                        'active' => 'Active',
                                    ])
                                    ->required()
                                    ->default('draft'),
                            ]),
                    ]),
                \Filament\Schemas\Components\Section::make('Content')
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('current_content')
                            ->label('Content')
                            ->rows(5)
                            ->required()
                            ->columnSpanFull(),
                        \Filament\Forms\Components\KeyValue::make('metadata')
                            ->columnSpanFull(),
                    ]),
                \Filament\Forms\Components\TextInput::make('organization')
                    ->label('Organization')
                    ->placeholder('e.g. vheins')
                    ->required(),

            ]);
    }
}
