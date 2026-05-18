@extends('merchant.default.layouts.app')

@section('title', 'Checkout Themes')

@section('content')
    <div id="settings-container">
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `Checkout Themes` }">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">Checkout Themes</h2>
                    <nav>
                        <ol class="flex items-center gap-1.5">
                            <li>
                                <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                                    href="{{ route('merchant.settings') }}">
                                    Brand Settings
                                    <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke=""
                                            stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </a>
                            </li>
                            <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">Checkout Themes</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Breadcrumb End -->

            <!-- Theme list grids -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($themes as $slug => $theme)
                    @php
                        $isActive = ($brand->theme === $slug);
                        $logoFile = !empty($theme['logo']) ? ltrim($theme['logo'], '/') : null;

                        // We check both the direct path and inside assets/ subfolder physically
                        $logoExists = false;
                        if ($logoFile) {
                            $directPath = resource_path('views/theme/' . $slug . '/' . $logoFile);
                            if (file_exists($directPath) && !is_dir($directPath)) {
                                $logoExists = true;
                            } else {
                                $assetsPath = resource_path('views/theme/' . $slug . '/assets/' . $logoFile);
                                if (file_exists($assetsPath) && !is_dir($assetsPath)) {
                                    $logoExists = true;
                                }
                            }
                        }
                    @endphp
                    <div class="relative flex flex-col rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs overflow-hidden transition-all duration-300 hover:shadow-md">
                        @if ($isActive)
                            <div class="absolute top-6 mt-3 right-6 inline-flex items-center rounded-full bg-brand-500 px-3 py-1 text-xs font-semibold text-white shadow-md gap-1.5 z-20">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                Active
                            </div>
                        @endif

                        <!-- Theme Screenshot/Logo Area -->
                        <div class="relative bg-gray-50 dark:bg-gray-900 w-full flex items-center justify-center border-b border-gray-100 dark:border-gray-800 overflow-hidden group" style="height: 240px;">
                            @if ($logoExists)
                                <img src="{{ route('module.asset', ['type' => 'checkout-theme', 'module' => $slug, 'path' => ltrim($theme['logo'], '/')]) }}" alt="{{ $theme['title'] }}"
                                    id="img-{{ $slug }}"
                                    class="w-full transition-transform duration-300 group-hover:scale-105"
                                    style="height: 240px; width: 100%; object-fit: cover;"
                                    onerror="this.style.display='none'; document.getElementById('placeholder-{{ $slug }}').style.display='flex';">

                                <div id="placeholder-{{ $slug }}" style="display: none; height: 240px; width: 100%;" class="absolute inset-0 bg-gray-50 dark:bg-gray-950 flex flex-col items-center justify-center text-gray-400 dark:text-gray-600 gap-2 p-6">
                                    <svg class="w-10 h-10 stroke-current text-gray-300 dark:text-gray-700" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">No Preview Available</span>
                                </div>
                            @else
                                <div class="absolute inset-0 bg-gray-50 dark:bg-gray-950 flex flex-col items-center justify-center text-gray-400 dark:text-gray-600 gap-2 p-6" style="height: 240px; width: 100%;">
                                    <svg class="w-10 h-10 stroke-current text-gray-300 dark:text-gray-700" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">No Preview Available</span>
                                </div>
                            @endif
                        </div>

                        <!-- Theme Info Content -->
                        <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ $theme['title'] }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Theme Slug: <code class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-[10px]">{{ $slug }}</code></p>

                                @if(!empty($theme['supported_languages']))
                                    <div class="mt-3 flex flex-wrap gap-1.5 items-center">
                                        <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase">Languages:</span>
                                        @foreach($theme['supported_languages'] as $langCode => $langName)
                                            <span class="inline-flex items-center text-[10px] font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md">
                                                {{ $langName }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                @if ($isActive)
                                    <a href="{{ route('merchant.settings.themes.manage', $slug) }}"
                                        class="flex-1 text-center bg-brand-500 hover:bg-brand-600 text-white text-sm font-bold py-2.5 px-4 rounded-lg shadow-theme-xs transition-all active:scale-95 flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Configure Settings
                                    </a>
                                @else
                                    <button onclick="activateTheme('{{ $slug }}', '{{ $theme['title'] }}')"
                                        class="flex-1 text-center border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-white/[0.03] hover:bg-gray-100 dark:hover:bg-white/[0.05] text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white text-sm font-bold py-2.5 px-4 rounded-lg shadow-theme-xs transition-all active:scale-95 flex items-center justify-center gap-2">
                                        Activate Theme
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Theme Activation Confirmation Modal -->
    <x-m::modal id="confirm-theme-modal" type="brand" title="Confirm Theme Activation"
        description="Are you sure you want to activate this checkout theme?"
        actionTitle="Activate Theme" actionId="confirm-activate-theme-btn" :cancelButtonShow="true" :isDispose="true">
        <x-slot name="icon">
            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
            </div>
        </x-slot>
    </x-m::modal>

    <!-- Activation Action Script -->
    <script>
        let activeThemeSlug = '';
        let activeThemeTitle = '';

        window.activateTheme = function(slug, title) {
            activeThemeSlug = slug;
            activeThemeTitle = title;

            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: {
                    id: 'confirm-theme-modal',
                    message: `Are you sure you want to activate the "${title}" checkout theme?`
                }
            }));
        };

        const confirmThemeBtn = document.getElementById('confirm-activate-theme-btn');
        if (confirmThemeBtn) {
            confirmThemeBtn.onclick = async function() {
                if (!activeThemeSlug) return;

                this.disabled = true;
                const originalText = this.innerHTML;
                this.innerHTML = `<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>`;

                try {
                    const response = await fetch("{{ route('merchant.settings.themes.active') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ slug: activeThemeSlug })
                    });

                    const data = await response.json();
                    if (data.status === 'true') {
                        showToast('success', `Theme "${activeThemeTitle}" activated successfully!`);
                        window.dispatchEvent(new CustomEvent('close-modal', {
                            detail: { id: 'confirm-theme-modal' }
                        }));
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showToast('error', data.message || 'Theme activation failed.');
                        this.disabled = false;
                        this.innerHTML = originalText;
                    }
                } catch (error) {
                    showToast('error', 'Network error. Please try again.');
                    this.disabled = false;
                    this.innerHTML = originalText;
                }
            };
        }
    </script>
@endsection
