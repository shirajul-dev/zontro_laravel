<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center']) }}>
    <div class="relative flex items-center justify-center">
        <!-- Outer Spinning Ring -->
        <div class="h-20 w-20 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>

        <!-- Inner Pulsing Circle with Favicon -->
        <div class="absolute h-14 w-14 overflow-hidden rounded-full bg-white p-1 shadow-lg dark:bg-gray-900 animate-pulse border border-gray-100 dark:border-gray-800">
            <img src="{{ asset('assets/images/favicon-dark.png') }}" alt="Loading..." class="h-full w-full object-contain rounded-full">
        </div>
    </div>

    @if($showText ?? false)
        <p class="mt-5 text-md font-medium text-gray-500 dark:text-gray-400 animate-pulse">
            {{ $text ?? '' }}
        </p>
    @endif
</div>
