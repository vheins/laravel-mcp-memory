<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->editProfileForm }}

        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-panels::form>

    <div class="mt-8">
        @livewire('profile.manage-mcp-tokens')
    </div>
</x-filament-panels::page>