<div x-data="{
        messages: [
            '👨‍🍳 Let him cook...',
            '✨ Manifesting this real quick...',
            '🧢 No cap, just straight facts...',
            '🎧 Vibing with the algorithm...',
            '🌟 Main character energy loading...',
            '🤫 Lowkey genius moves...',
            '🤝 Bet, working on it...',
            '🥶 Sheesh, hold up...',
            '🔄 Trust the process...',
            '🧠 It’s giving intelligence...',
            '🤯 Big brain energy incoming...',
            '🚀 Sending it...',
            '📸 Caught in 4k being smart...',
            '💪 Slight flex, but okay...',
            '🎵 Hits different when it loads...',
            '👔 CEO of processing...',
            '👀 IYKYK...',
            '🤩 Stan this memory...',
            '🔑 High key crunching data...',
            '😎 W Rizz algorithm...',
            '💅 Slay queen (of data)...',
            '✅ Passing the vibe check...',
            '🔓 Gatekeeping nothing...',
            '🏠 Living rent free in the cloud...',
            '🌱 Touching grass (virtually)...',
            '💨 Yeeting data into existence...',
            '👁️ POV: You\'re waiting for magic...',
            '🤷 Sorry not sorry, just thinking...',
            '✨ Glow up in progress...',
            '📚 Era: Intellectual...',
            '💧 Validating the drip...',
            '📝 Understanding the assignment...',
            '💯 Real ones know...',
            '📉 Ratioing the error rate...',
            '🍽️ Ate that...'
        ],
        colors: [
            'blue',
            'green',
            'purple',
            'amber',
            'pink',
            'indigo',
            'teal',
            'rose',
        ],
        current: 'Thinking...',
        currentColor: '',
        getColorClass(color) {
            return {
                blue: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                green: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                purple: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
                amber: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                pink: 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300',
                indigo: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300',
                teal: 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300',
                rose: 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
            }[color];
        },
        init() {
            this.current = this.messages[Math.floor(Math.random() * this.messages.length)];
            this.currentColor = this.getColorClass(this.colors[Math.floor(Math.random() * this.colors.length)]);

            const loop = () => {
                // Random delay between 1000ms and 2500ms
                const delay = Math.floor(Math.random() * 1500) + 1000;

                setTimeout(() => {
                    this.current = this.messages[Math.floor(Math.random() * this.messages.length)];
                    this.currentColor = this.getColorClass(this.colors[Math.floor(Math.random() * this.colors.length)]);
                    loop();
                }, delay);
            }
            loop();
        }
    }" class="flex items-center justify-center py-4 space-x-2" wire:loading>

    <span x-text="current" :class="currentColor"
        class="px-3 py-1 rounded-full text-sm font-medium animate-pulse transition-colors duration-500 shadow-sm">Thinking...</span>
</div>