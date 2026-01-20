<?php

namespace App\Filament\Resources\Memories\Schemas;

use App\Enums\MemoryScope;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
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
                                    ->options(MemoryScope::class)
                                    ->required(),
                                \Filament\Forms\Components\Select::make('memory_type')
                                    ->options(MemoryType::class)
                                    ->required(),
                            ]),
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                \Filament\Forms\Components\Select::make('status')
                                    ->options(MemoryStatus::class)
                                    ->required()
                                    ->default(MemoryStatus::Draft),
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
