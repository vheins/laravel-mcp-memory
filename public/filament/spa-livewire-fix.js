document.addEventListener('filament:navigated', () => {
    if (!window.livewireScriptConfig) {
        const token =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');

        window.livewireScriptConfig = {
            uri: '/livewire/update',
            csrf: token,
        };
    }
});
