<div {{ $attributes->merge(['class' => 'animate-pulse']) }}>
    <div class="space-y-6">
        <!-- Header Shimmer -->
        <div class="flex items-center justify-between mb-8">
            <div class="space-y-3 w-full max-w-md">
                <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded-lg w-3/4"></div>
                <div class="h-4 bg-gray-100 dark:bg-gray-800 rounded-md w-full"></div>
            </div>
            <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded-lg w-32"></div>
        </div>

        <!-- Form Grid Shimmer -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @for ($i = 0; $i < 6; $i++)
                <div class="space-y-3">
                    <div class="h-4 bg-gray-100 dark:bg-gray-800 rounded w-24"></div>
                    <div class="h-12 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl w-full"></div>
                </div>
            @endfor
        </div>

        <!-- Large Area Shimmer -->
        <div class="space-y-3 mt-8">
            <div class="h-4 bg-gray-100 dark:bg-gray-800 rounded w-32"></div>
            <div class="h-32 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl w-full"></div>
        </div>
    </div>
</div>
