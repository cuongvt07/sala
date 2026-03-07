<div class="flex items-center gap-3">
    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md ring-2 ring-white">
        {{ substr($siteName ?? config('app.name'), 0, 1) }}
    </div>
    <span class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
        {{ $siteName ?? config('app.name') }}
    </span>
</div>
