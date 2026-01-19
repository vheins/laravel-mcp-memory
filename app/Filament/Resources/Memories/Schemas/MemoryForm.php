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
                        \Filament\Forms\Components\Select::make('repository')
                            ->label('Repository')
                            ->relationship('repositoryRel', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        \Filament\Forms\Components\Select::make('user')
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
                                    ])
                                    ->required(),
                                \Filament\Forms\Components\Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'verified' => 'Verified',
                                        'locked' => 'Locked',
                                        'deprecated' => 'Deprecated',
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
                \Filament\Forms\Components\Hidden::make('created_by_type')
                    ->default('human'),
                \Filament\Forms\Components\Hidden::make('organization')
                     // Logic to auto-fill organization needed? Or nullable?
                     // Migration says organization_id is UUID, required (not nullable).
                     // Ideally, we get organization from repository relation or user context.
                     // For now, let's make it visible/required if not inferred.
                     // Actually, let's make it a hidden field defaulted or handled by Observer/Service.
                     // But Standard Filament create checks validation.
                     // Let's add it to form for now as a Select or hidden if we can infer it.
                     // Given MCP architecture, manual creation implies human admin.
                     // Let's make it a Select for now to be safe, or just infer from Repository if possible.
                     // Simplest: Select Organization.
                     // temporary default
                     ->required(),
                \Filament\Forms\Components\TextInput::make('organization')
                    ->required()
                    ->default('d2b7dcdf-1c54-45ff-b907-c181e5a829ea')
                    ->hidden(),

            ]);
    }
}
