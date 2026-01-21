document.addEventListener('filament:navigated', () => {
    if (!window.livewireScriptConfig) {
        const meta = document.querySelector('meta[name="livewire:csrf"]');

        window.livewireScriptConfig = {
            uri: '/livewire/update',
            csrf: meta?.getAttribute('content'),
        };
    }
});
