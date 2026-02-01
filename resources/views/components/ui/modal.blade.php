@props([
    'name', 
    'title' => '', 
    'width' => 'max-w-2xl'
])

<div
    x-data="{ show: @entangle($name) }"
    x-show="show"
    x-on:keydown.escape.window="show = false"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <!-- Backdrop -->
    <div x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" 
         @click="show = false">
    </div>

    <!-- Modal Panel -->
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        
        <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all w-full {{ $width }} sm:my-8 border border-gray-100">
            <!-- Header -->
            @if($title)
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b border-gray-100">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">
                        {{ $title }}
                    </h3>
                    <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            <!-- Content -->
            <div class="px-4 py-5 sm:p-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
