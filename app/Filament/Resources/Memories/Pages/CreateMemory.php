<?php

namespace App\Filament\Resources\Memories\Pages;

use App\Filament\Resources\Memories\MemoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateMemory extends CreateRecord
{
    protected static string $resource = MemoryResource::class;
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('generateMemory')
                ->label('Generate with AI')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\Textarea::make('prompt')
                        ->label('Describe the memory you want to create')
                        ->required()
                        ->rows(4)
                        ->placeholder('e.g., Create a documentation for the Auth authentication flow...'),
                ])
                ->action(function (array $data, \App\Services\GeminiService $geminiService) {
                    try {
                        $generatedData = $geminiService->generateMemoryFromPrompt($data['prompt']);
                        Log::info('Generated Memory', $generatedData);

                        // Auto-fill user and defaults
                        $generatedData['user_id'] = auth()->id();

                        // Fallback defaults if AI misses them
                        $generatedData['scope_type'] = $generatedData['scope_type'] ?? 'user';
                        $generatedData['memory_type'] = $generatedData['memory_type'] ?? 'documentation_ref';
                        $generatedData['status'] = $generatedData['status'] ?? 'active';
                        $generatedData['importance'] = $generatedData['importance'] ?? 5;

                        $this->form->fill($generatedData);

                        \Filament\Notifications\Notification::make()
                            ->title('Memory Generated Successfully')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Generation Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

}
