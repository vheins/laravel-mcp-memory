<?php

namespace App\Filament\Resources\Memories\Schemas;

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
                        \Filament\Forms\Components\TextInput::make('organization')
                            ->label('Organization')
                            ->placeholder('e.g. vheins')
                            ->required(),
                        \Filament\Schemas\Components\Grid::make(2)
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
                            ]),
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
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
                                \Filament\Forms\Components\Slider::make('importance')
                                    ->label('Importance Score')
                                    ->minValue(1)
                                    ->maxValue(10)
                                    ->step(1)
                                    ->default(1),
                            ]),
                    ]),
                \Filament\Schemas\Components\Section::make('Content')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->placeholder('Brief summary of the memory')
                            ->required()
                            ->columnSpanFull(),
                        \Filament\Forms\Components\MarkdownEditor::make('current_content')
                            ->label('Content')
                            ->toolbarButtons([
                                'attachFiles',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'heading',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'table',
                                'undo',
                            ])
                            ->required()
                            ->columnSpanFull(),
                        \Filament\Forms\Components\KeyValue::make('metadata')
                            ->columnSpanFull(),
                        \Filament\Forms\Components\Select::make('relatedMemories')
                            ->label('Related Memories (Knowledge Graph)')
                            ->relationship('relatedMemories', 'title')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->title ?? substr($record->current_content, 0, 50).'...')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ]),

            ]);
    }
}
