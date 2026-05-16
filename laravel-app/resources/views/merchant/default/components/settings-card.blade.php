@props([
    'href' => '#',
    'title' => '',
    'description' => '',
])

<a href="{{ $href }}"
    {{ $attributes->merge(['class' => 'group relative p-8 border-b border-t border-r border-l border-gray-100 dark:border-gray-800 transition-all duration-300 hover:z-20 hover:border-brand-500 dark:hover:border-brand-500']) }}>
    <!-- Content Wrapper -->
    <div class="relative z-10">
        <div
            class="setting-card-icon bg-gray-100 mb-6 flex h-[52px] w-[52px] items-center justify-center rounded-sm">
            {{ $icon }}
        </div>
        <h3 class="mb-2 text-theme-xl font-medium text-gray-800 dark:text-white/90">
            {{ $title }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $description }}
        </p>
    </div>
</a>
