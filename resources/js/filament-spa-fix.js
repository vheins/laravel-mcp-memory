// Initialize livewireScriptConfig immediately on page load to prevent undefined errors
const initializeLivewireConfig = () => {
    if (!window.livewireScriptConfig) {
        const meta = document.querySelector('meta[name="livewire:csrf"]');

        window.livewireScriptConfig = {
            uri: '/livewire/update',
            csrf: meta?.getAttribute('content'),
        };
    }
};

// Initialize immediately
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeLivewireConfig);
} else {
    initializeLivewireConfig();
}

// Also initialize on filament:navigated for SPA navigation
document.addEventListener('filament:navigated', initializeLivewireConfig);
