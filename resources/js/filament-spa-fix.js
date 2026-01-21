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

// Handle Chart.js cleanup issues in production
// Wrap Chart.js destroy to prevent null reference errors during SPA navigation
if (window.Chart) {
    const originalDestroy = window.Chart.prototype.destroy;
    window.Chart.prototype.destroy = function () {
        if (this && typeof originalDestroy === 'function') {
            try {
                originalDestroy.call(this);
            } catch (e) {
                // Silently handle errors during chart cleanup
                console.debug('Chart destroy error (safe to ignore):', e.message);
            }
        }
    };
}
