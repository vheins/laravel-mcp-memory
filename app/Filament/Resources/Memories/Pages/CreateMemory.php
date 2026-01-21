<?php

declare(strict_types=1);

namespace App\Filament\Resources\Memories\Pages;

use App\Filament\Resources\Memories\MemoryResource;
use App\Services\GeminiService;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMemory extends CreateRecord
{
    protected static string $resource = MemoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateMemory')
                ->label('Generate with AI')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->form([
                    Textarea::make('prompt')
                        ->label('Describe the memory you want to create')
                        ->required()
                        ->rows(4)
                        ->placeholder('e.g., Create a documentation for the Auth authentication flow...'),
                    ViewField::make('ai_loader')
                        ->view('filament.resources.memories.components.ai-loader')
                        ->hiddenLabel()
                        ->dehydrated(false),
                ])
                ->action(function (array $data, GeminiService $geminiService): void {
                    try {
                        $generatedData = $geminiService->generateMemoryFromPrompt($data['prompt']);

                        // Auto-fill user and defaults
                        $generatedData['user_id'] = auth()->id();

                        // Fallback defaults if AI misses them
                        $generatedData['scope_type'] ??= 'user';
                        $generatedData['memory_type'] ??= 'documentation_ref';
                        $generatedData['status'] ??= 'active';
                        $generatedData['importance'] ??= 5;

                        $this->form->fill($generatedData);

                        Notification::make()
                            ->title('Memory Generated Successfully')
                            ->success()
                            ->send();

                    } catch (Exception $exception) {
                        Notification::make()
                            ->title('Generation Failed')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
