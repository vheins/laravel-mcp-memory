<?php

namespace App\Filament\Resources\Memories\Pages;

use App\Filament\Resources\Memories\MemoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMemory extends EditRecord
{
    protected static string $resource = MemoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('enhanceMemory')
                ->label('Enhance with AI')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('shortcut')
                        ->label('Quick Action')
                        ->options([
                            'optimize_code' => 'Optimize Code & Logic',
                            'add_documentation' => 'Add Documentation & Comments',
                            'refactor_clean' => 'Refactor for Readability',
                            'summarize_concise' => 'Summarize & Make Concise',
                            'redact_secrets' => 'Redact Sensitive Data',
                            'generate_examples' => 'Generate Usage Examples',
                            'explain_code' => 'Explain Code Flow',
                            'format_tech' => 'Format as Technical Markdown',
                        ])
                        ->reactive()
                        ->afterStateUpdated(function ($state, $set) {
                            $instructions = [
                                'optimize_code' => 'Analyze the code in the content and suggest optimizations for performance and readability.',
                                'add_documentation' => 'Add comprehensive DocBlocks, inline comments, and description to the code snippets.',
                                'refactor_clean' => 'Refactor the code to improve readability and maintainability (Clean Code).',
                                'summarize_concise' => 'Summarize the content to be more concise and to-the-point, removing unnecessary fluff while retaining key information.',
                                'redact_secrets' => 'Identify and redact keys, tokens, passwords, IPs, or PII from the content.',
                                'generate_examples' => 'Generate practical usage examples and scenarios for the code or concept.',
                                'explain_code' => 'Explain the logic and flow of the code step-by-step in plain English.',
                                'format_tech' => 'Format the content using standard Technical Markdown with proper language tags.',
                            ];

                            if (isset($instructions[$state])) {
                                $set('instruction', $instructions[$state]);
                            }
                        }),
                    \Filament\Forms\Components\Textarea::make('instruction')
                        ->label('Enhancement Instruction')
                        ->required()
                        ->default('Improve grammar and clarity.')
                        ->rows(4),
                ])
                ->action(function (array $data, \App\Models\Memory $record) {
                     $geminiService = app(\App\Services\GeminiService::class);

                     \Filament\Notifications\Notification::make()
                        ->title('Enhancing Memory...')
                        ->body('Please wait while the AI enhances the content.')
                        ->info()
                        ->send();

                     try {
                        // Use fresh data
                        $currentData = $record->toArray();
                        $enhancedData = $geminiService->enhanceMemory($currentData, $data['instruction']);

                        $record->update($enhancedData);
                        $this->fillForm(); // Refresh form with new data

                        \Filament\Notifications\Notification::make()
                            ->title('Memory Enhanced')
                            ->success()
                            ->send();

                     } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Enhancement Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                     }
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }


}
