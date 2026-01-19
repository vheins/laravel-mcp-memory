<?php

use Livewire\Volt\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Filament\Notifications\Notification;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;

new class extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public ?string $plainTextToken = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(Auth::user()->tokens()->getQuery())
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('abilities')
                    ->badge()
                    ->label('Scopes'),
                TextColumn::make('last_used_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never used'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('createToken')
                    ->label('Generate MCP Token')
                    ->form([
                        TextInput::make('name')
                            ->required()
                            ->placeholder('e.g. VS Code Laptop')
                            ->maxLength(255),
                        CheckboxList::make('abilities')
                            ->options([
                                'mcp:read' => 'Read Access (mcp:read)',
                                'mcp:write' => 'Write Access (mcp:write)',
                                'mcp:admin' => 'Admin Access (mcp:admin) - Human Only',
                            ])
                            ->default(['mcp:read', 'mcp:write'])
                            ->required()
                            ->columns(1),
                    ])
                    ->action(function (array $data) {
                        $token = Auth::user()->createToken($data['name'], $data['abilities']);

                        $this->plainTextToken = $token->plainTextToken;

                        $this->dispatch('token-generated');

                        Notification::make()
                            ->title('Token generated successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Action::make('revoke')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Revoke Token')
                    ->modalDescription('Are you sure you want to revoke this token? Any agent using this token will lose access immediately.')
                    ->action(fn (PersonalAccessToken $record) => $record->delete()),
            ])
            ->emptyStateHeading('No MCP tokens active')
            ->emptyStateDescription('Generate a token to allow AI agents to securely access your MCP Memory Server.');
    }
}; ?>

<div class="space-y-6">
    <x-filament::section>
        <x-slot name="heading">
            MCP Access Tokens
        </x-slot>

        <x-slot name="description">
            Manage long-lived tokens for AI agents (Cursor, VS Code, etc.) to access your MCP Memory.
        </x-slot>

        {{ $this->table }}
    </x-filament::section>

    <x-filament::modal id="token-success-modal" alignment="center" width="xl" :display-if="$plainTextToken">
        <x-slot name="heading">
            Token Generated Successfully
        </x-slot>

        <div class="space-y-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Please copy your new token now. For your security, it won't be shown again.
            </p>

            <div class="flex items-center gap-2 p-3 bg-gray-100 dark:bg-gray-800 rounded-lg">
                <code class="flex-1 text-xs font-mono break-all">{{ $plainTextToken }}</code>

                <x-filament::icon-button icon="heroicon-o-clipboard-document" color="gray"
                    x-on:click="window.navigator.clipboard.writeText('{{ $plainTextToken }}'); $tooltip('Copied!', { timeout: 1500 })" />
            </div>

            <p class="text-xs text-danger-600 dark:text-danger-400 font-medium italic">
                ! Save this token now. You will not be able to see it again.
            </p>
        </div>

        <x-slot name="footer">
            <x-filament::button color="gray" x-on:click="close()">
                Done
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('token-generated', (event) => {
                $dispatch('open-modal', { id: 'token-success-modal' });
            });
        });
    </script>
</div>