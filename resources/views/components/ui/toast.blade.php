<div x-data="toastComponent()"
     @toast.window="add($event.detail)"
     class="fixed top-6 right-6 z-[100] flex flex-col gap-3 pointer-events-none"
     x-cloak>
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-8 scale-95"
             x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 transform translate-x-8 scale-95"
             class="pointer-events-auto flex items-center gap-4 px-5 py-4 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.2)] border min-w-[320px] max-w-md bg-white backdrop-blur-xl"
             :class="{
                'border-green-100 bg-white/95': toast.type === 'success',
                'border-red-100 bg-white/95': toast.type === 'error',
                'border-blue-100 bg-white/95': toast.type === 'info',
                'border-amber-100 bg-white/95': toast.type === 'warning'
             }">
            
            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                 :class="{
                    'bg-green-100 text-green-600': toast.type === 'success',
                    'bg-red-100 text-red-600': toast.type === 'error',
                    'bg-blue-100 text-blue-600': toast.type === 'info',
                    'bg-amber-100 text-amber-600': toast.type === 'warning'
                 }">
                <template x-if="toast.type === 'success'"><x-icon name="heroicon-s-check-circle" class="h-6 w-6" /></template>
                <template x-if="toast.type === 'error'"><x-icon name="heroicon-s-x-circle" class="h-6 w-6" /></template>
                <template x-if="toast.type === 'info'"><x-icon name="heroicon-s-information-circle" class="h-6 w-6" /></template>
                <template x-if="toast.type === 'warning'"><x-icon name="heroicon-s-exclamation-triangle" class="h-6 w-6" /></template>
            </div>

            <div class="flex-1">
                <div class="text-sm font-bold text-gray-900 leading-tight" x-text="toast.message"></div>
                <div class="text-[10px] text-gray-500 mt-0.5 uppercase tracking-wider font-semibold" x-text="toast.type"></div>
            </div>

            <button @click="remove(toast.id)" class="flex-shrink-0 p-1 rounded-full hover:bg-gray-100 text-gray-400 transition-colors">
                <x-icon name="heroicon-o-x-mark" class="h-4 w-4" />
            </button>
        </div>
    </template>
</div>

<script>
function toastComponent() {
    return {
        toasts: [],
        add(detail) {
            const id = Date.now();
            const type = detail.type || 'success';
            const message = typeof detail === 'string' ? detail : (detail.message || '');
            
            this.toasts.push({ 
                id, 
                type, 
                message, 
                show: false 
            });

            // Trigger animation on next tick
            this.$nextTick(() => {
                const index = this.toasts.findIndex(t => t.id === id);
                if (index !== -1) this.toasts[index].show = true;
            });

            // Auto-remove after 2 seconds
            setTimeout(() => {
                this.remove(id);
            }, 2000);
        },
        remove(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index !== -1) {
                this.toasts[index].show = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        }
    }
}
</script>
