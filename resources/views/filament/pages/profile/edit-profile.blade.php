<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->editProfileForm }}

        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-panels::form>

</x-filament-panels::page>