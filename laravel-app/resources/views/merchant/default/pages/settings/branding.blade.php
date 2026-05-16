@extends('merchant.default.layouts.app')

@section('title', 'Brand & Logos')

@section('content')
    <div id="settings-container">
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `Brand & Logos` }">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">Brand & Logos</h2>
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
                            <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">Brand & Logos</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Breadcrumb End -->

            <div class="max-w-4xl">
                <form id="branding-settings-form" class="space-y-6">
                    @csrf

                    <div
                        class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Visual Assets</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Manage your brand logo and favicon.</p>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                <!-- Logo Upload -->
                                <div class="space-y-3">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Brand
                                        Logo</label>
                                    <div class="relative group">
                                        <div id="logo-preview-container"
                                            class="flex items-center justify-center w-full h-44 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50 dark:bg-gray-900/50 dark:border-gray-800 group-hover:border-brand-500 transition-all cursor-pointer overflow-hidden shadow-inner">
                                            @if ($brand->logo)
                                                <img src="{{ $brand->logo }}" id="logo-preview-img" alt="Logo"
                                                    class="max-h-full max-w-full p-4 object-contain transition-transform group-hover:scale-105">
                                            @else
                                                <div class="text-center space-y-2" id="logo-placeholder">
                                                    <div class="flex justify-center">
                                                        <div
                                                            class="p-3 rounded-full bg-white dark:bg-gray-800 shadow-sm text-gray-400">
                                                            <svg class="w-8 h-8" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <p class="text-xs font-medium text-gray-500">Upload Logo</p>
                                                </div>
                                            @endif
                                        </div>
                                        <input type="file" name="logo"
                                            class="absolute inset-0 opacity-0 cursor-pointer"
                                            onchange="previewImage(this, 'logo-preview-img', 'logo-placeholder')">
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Recommended: 250x100px (PNG/JPG)
                                    </div>
                                </div>

                                <!-- Favicon Upload -->
                                <div class="space-y-3">
                                    <label
                                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Favicon</label>
                                    <div class="relative group">
                                        <div id="favicon-preview-container"
                                            class="flex items-center justify-center w-32 h-32 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50 dark:bg-gray-900/50 dark:border-gray-800 group-hover:border-brand-500 transition-all cursor-pointer overflow-hidden shadow-inner">
                                            @if ($brand->favicon)
                                                <img src="{{ $brand->favicon }}" id="favicon-preview-img" alt="Favicon"
                                                    class="w-16 h-16 object-contain transition-transform group-hover:scale-110">
                                            @else
                                                <div class="text-center space-y-1" id="favicon-placeholder">
                                                    <div class="flex justify-center">
                                                        <div
                                                            class="p-2 rounded-full bg-white dark:bg-gray-800 shadow-sm text-gray-400">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <p class="text-[10px] font-medium text-gray-500">Favicon</p>
                                                </div>
                                            @endif
                                        </div>
                                        <input type="file" name="favicon"
                                            class="absolute inset-0 opacity-0 cursor-pointer"
                                            onchange="previewImage(this, 'favicon-preview-img', 'favicon-placeholder')">
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Square (32x32px)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex justify-end p-6 bg-gray-50/50 dark:bg-gray-800/20 border-t border-gray-100 dark:border-gray-800">
                            <button type="submit"
                                class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-10 py-3 text-sm font-bold text-white transition-all active:scale-95">
                                Update Branding
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function previewImage(input, previewId, placeholderId) {
                const file = input.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        let img = document.getElementById(previewId);
                        const placeholder = document.getElementById(placeholderId);

                        if (!img) {
                            img = document.createElement('img');
                            img.id = previewId;
                            img.className = previewId.includes('logo') ?
                                'max-h-full max-w-full p-4 object-contain transition-transform group-hover:scale-105' :
                                'w-16 h-16 object-contain transition-transform group-hover:scale-110';
                            input.parentElement.querySelector('div').appendChild(img);
                        }

                        img.src = e.target.result;
                        img.classList.remove('hidden');
                        if (placeholder) placeholder.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            }

            document.getElementById('branding-settings-form').onsubmit = async function(e) {
                e.preventDefault();
                const btn = this.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML =
                    '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Updating...';

                try {
                    const formData = new FormData(this);
                    const response = await fetch("{{ route('merchant.settings.branding.update') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        showToast('success', 'Branding assets updated successfully');
                    } else {
                        showToast('error', data.message || 'Update failed');
                    }
                } catch (error) {
                    showToast('error', 'Network error. Please try again.');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            };
        </script>

    </div>
@endsection
