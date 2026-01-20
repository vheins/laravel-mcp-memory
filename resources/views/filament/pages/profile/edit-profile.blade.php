<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->editProfileForm }}

        <div class="flex flex-wrap items-center gap-4 justify-start">
            @foreach ($this->getCachedFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>

</x-filament-panels::page>