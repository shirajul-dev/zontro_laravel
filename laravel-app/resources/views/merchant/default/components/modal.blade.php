@props([
    'id' => 'modal-' . uniqid(),
    'title' => '',
    'description' => '',
    'type' => 'success', // success, error, brand
    'show' => false,
    'isDispose' => true,
    'actionTitle' => null,
    'actionRoute' => null,
    'actionId' => null,
    'cancelButtonShow' => false,
    'cancelButtonTitle' => 'Cancel'
])

<div x-data="{
         isModalOpen: {{ $show ? 'true' : 'false' }},
         isDispose: {{ $isDispose ? 'true' : 'false' }},
         dynamicTitle: '{{ $title }}',
         dynamicMessage: '{{ $description }}'
     }"
     x-on:open-modal.window="if($event.detail.id === '{{ $id }}') {
         isModalOpen = true;
         if($event.detail.title) dynamicTitle = $event.detail.title;
         if($event.detail.message) dynamicMessage = $event.detail.message;
     }"
     x-on:close-modal.window="if($event.detail.id === '{{ $id }}') isModalOpen = false"
     x-on:keydown.escape.window="isModalOpen = false"
     class="inline-block">

    <div x-show="isModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999"
         style="display: none;">

        <!-- Backdrop -->
        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" @click="isDispose && (isModalOpen = false)"></div>

        <!-- Modal Content -->
        <div x-show="isModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.outside="isDispose && (isModalOpen = false)"
             class="relative max-w-[600px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">

            <!-- Close Button -->
            <button x-show="isDispose" @click="isModalOpen = false"
                    class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11">
                <svg class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z" fill=""></path>
                </svg>
            </button>

            <div>
                <!-- Icon Section (Optional) -->
                @if(isset($icon))
                    <div class="flex items-center justify-center mb-6">
                        {{ $icon }}
                    </div>
                @endif

                @if($title)
                    <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90" x-text="dynamicTitle">
                        {{ $title }}
                    </h4>
                @endif

                @if($description)
                    <p class="text-sm leading-6 text-gray-500 dark:text-gray-400 mb-6" x-show="dynamicMessage" x-text="dynamicMessage">
                        {{ $description }}
                    </p>
                @endif

                <div class="modal-body">
                    {{ $slot }}
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end w-full gap-3 mt-6">
                    @if($cancelButtonShow)
                        <button @click="isModalOpen = false" type="button"
                                class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto">
                            {{ $cancelButtonTitle }}
                        </button>
                    @endif

                    @if($actionRoute)
                        <a href="{{ $actionRoute }}" id="{{ $actionId }}"
                           class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                            {{ $actionTitle ?? 'Save Changes' }}
                        </a>
                    @else
                        <button type="button" id="{{ $actionId }}" @click="isModalOpen = false"
                                class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                            {{ $actionTitle ?? 'Save Changes' }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
