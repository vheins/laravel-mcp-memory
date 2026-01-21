<?php

declare(strict_types=1);

namespace App\Filament\Resources\Memories\Schemas;

use App\Enums\MemoryScope;
use App\Enums\MemoryStatus;
use App\Enums\MemoryType;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Context')
                    ->schema([
                        TextInput::make('repository')
                            ->label('Repository')
                            ->placeholder('e.g. owner/repo')
                            ->helperText('Free text repository identifier (e.g. vheins/laravel-mcp-memory)'),
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('userRel', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('organization')
                            ->label('Organization')
                            ->placeholder('e.g. vheins')
                            ->required(),
                        Grid::make(2)
                            ->schema([
                                Select::make('scope_type')
                                    ->options(MemoryScope::class)
                                    ->required(),
                                Select::make('memory_type')
                                    ->options(MemoryType::class)
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->options(MemoryStatus::class)
                                    ->required()
                                    ->default(MemoryStatus::Draft),
                                Slider::make('importance')
                                    ->label('Importance Score')
                                    ->minValue(1)
                                    ->maxValue(10)
                                    ->step(1)
                                    ->default(1),
                            ]),
                    ]),
                Section::make('Content')
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->placeholder('Brief summary of the memory')
                            ->required()
                            ->columnSpanFull(),
                        MarkdownEditor::make('current_content')
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
                        KeyValue::make('metadata')
                            ->columnSpanFull(),
                        Select::make('relatedMemories')
                            ->label('Related Memories (Knowledge Graph)')
                            ->relationship('relatedMemories', 'title')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->title ?? substr((string) $record->current_content, 0, 50) . '...')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ]),

            ]);
    }
}
