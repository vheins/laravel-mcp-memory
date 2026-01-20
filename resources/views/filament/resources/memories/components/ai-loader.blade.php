<div x-data="{
        messages: [
            'Let him cook...',
            'Manifesting this real quick...',
            'No cap, just straight facts...',
            'Vibing with the algorithm...',
            'Main character energy loading...',
            'Lowkey genius moves...',
            'Bet, working on it...',
            'Sheesh, hold up...',
            'Trust the process...',
            'It’s giving intelligence...'
        ],
        current: 'Thinking...',
        init() {
            this.current = this.messages[0];
            setInterval(() => {
                this.current = this.messages[Math.floor(Math.random() * this.messages.length)];
            }, 1800);
        }
    }" class="flex items-center justify-center py-4 space-x-2 text-primary-600 dark:text-primary-400" wire:loading>
    <span x-text="current" class="font-medium animate-pulse">Thinking...</span>
</div>