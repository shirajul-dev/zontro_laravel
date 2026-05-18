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
                <form id="branding-settings-form" class="space-y-6" enctype="multipart/form-data">
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
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Logo Upload -->
                                <div class="space-y-3">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-400">
                                        Brand Logo
                                    </label>
                                    <div class="space-y-3">
                                        <!-- Standard File Input Field -->
                                        <div class="relative flex items-center justify-between border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-lg p-3 shadow-theme-xs">
                                            <input type="file" name="logo" id="logo-input"
                                                class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                                onchange="previewImage(this, 'logo-preview-img', 'logo-preview-container')">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300" id="logo-filename-label">Choose a file...</p>
                                                    <p class="text-[10px] text-gray-400">PNG, JPG, JPEG up to 2MB</p>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                Browse
                                            </span>
                                        </div>

                                        <!-- Preview Container at Bottom -->
                                        <div id="logo-preview-container" class="relative border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50 dark:bg-gray-950 p-4 flex items-center justify-center overflow-hidden" style="height: 140px; max-width: 320px;">
                                            <img src="{{ $brand->logo ?: asset('assets/images/logo-light.png') }}" id="logo-preview-img" alt="Logo Preview"
                                                class="max-h-full max-w-full object-contain rounded-lg shadow-sm">
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Recommended: 250x100px (PNG/JPG)
                                    </div>
                                </div>

                                <!-- Favicon Upload -->
                                <div class="space-y-3">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-400">
                                        Favicon
                                    </label>
                                    <div class="space-y-3">
                                        <!-- Standard File Input Field -->
                                        <div class="relative flex items-center justify-between border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-lg p-3 shadow-theme-xs">
                                            <input type="file" name="favicon" id="favicon-input"
                                                class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                                onchange="previewImage(this, 'favicon-preview-img', 'favicon-preview-container')">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300" id="favicon-filename-label">Choose a file...</p>
                                                    <p class="text-[10px] text-gray-400">PNG, JPG, ICO up to 1MB</p>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                Browse
                                            </span>
                                        </div>

                                        <!-- Preview Container at Bottom -->
                                        <div id="favicon-preview-container" class="relative border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50 dark:bg-gray-950 p-4 flex items-center justify-center overflow-hidden" style="height: 140px; max-width: 140px;">
                                            <img src="{{ $brand->favicon ?: asset('assets/images/favicon-dark.png') }}" id="favicon-preview-img" alt="Favicon Preview"
                                                class="max-h-full max-w-full object-contain rounded-lg shadow-sm" style="height: 64px; width: 64px;">
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Square (32x32px)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('merchant.settings') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold text-white transition-all active:scale-95">
                            Update Branding
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function previewImage(input, previewId, containerId) {
                const file = input.files[0];
                const label = document.getElementById(input.name + '-filename-label');
                if (file) {
                    if (label) {
                        label.textContent = file.name;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.getElementById(previewId);
                        const container = document.getElementById(containerId);
                        
                        if (img) {
                            img.src = e.target.result;
                        }
                        if (container) {
                            container.classList.remove('hidden');
                        }
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
