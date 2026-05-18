@extends('merchant.default.layouts.app')

@section('title', 'Theme Settings: ' . $themeInfo['title'])

@section('content')
    <div id="settings-container">
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: 'Configure {{ $themeInfo['title'] }}' }">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">Configure Theme</h2>
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
                            <li>
                                <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                                    href="{{ route('merchant.settings.themes') }}">
                                    Checkout Themes
                                    <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke=""
                                            stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </a>
                            </li>
                            <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">Configure Theme</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Breadcrumb End -->

            <div class="max-w-4xl">
                <form id="theme-settings-form" class="space-y-6 max-w-4xl" enctype="multipart/form-data">
                    @csrf

                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs overflow-hidden">
                        <!-- Card Header -->
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">{{ $themeInfo['title'] }} Theme Settings</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Configure visual themes, custom colors, and footer copy for hosted checkout pages.</p>
                            </div>
                        </div>

                        <!-- Card Fields Content -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                @foreach ($fields as $field)
                                    <div class="space-y-2 {{ in_array($field['type'], ['textarea', 'image']) ? 'col-span-full' : '' }}">
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-400">
                                            {{ $field['label'] }}
                                            @if (!empty($field['required']))
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>

                                        <!-- Render SELECT drop list -->
                                        @if ($field['type'] === 'select')
                                            <div class="relative z-20">
                                                <select name="{{ $field['name'] }}" 
                                                    class="h-11 w-full appearance-none rounded-lg border border-gray-200 bg-transparent px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:text-white/90 shadow-theme-xs"
                                                    @if(!empty($field['required'])) required @endif>
                                                    @foreach ($field['options'] as $optVal => $optLabel)
                                                        <option value="{{ $optVal }}" {{ ($field['value'] ?? '') === $optVal ? 'selected' : '' }} class="dark:bg-gray-900">
                                                            {{ $optLabel }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                                    <svg class="stroke-current" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                                                </span>
                                            </div>

                                        <!-- Render IMAGE upload zone -->
                                        @elseif ($field['type'] === 'image')
                                            <div class="space-y-3">
                                                <!-- Standard File Input Field -->
                                                <div class="relative flex items-center justify-between border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-lg p-3 shadow-theme-xs">
                                                    <input type="file" name="{{ $field['name'] }}" id="{{ $field['name'] }}-input"
                                                        class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                                        onchange="previewImage(this, '{{ $field['name'] }}-preview-img', '{{ $field['name'] }}-preview-container')">
                                                    <div class="flex items-center gap-3">
                                                        <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-500">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300" id="{{ $field['name'] }}-filename-label">Choose a file...</p>
                                                            <p class="text-[10px] text-gray-400">PNG, JPG, JPEG up to 2MB</p>
                                                        </div>
                                                    </div>
                                                    <span class="inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                        Browse
                                                    </span>
                                                </div>

                                                <!-- Preview Container at Bottom -->
                                                <div id="{{ $field['name'] }}-preview-container" class="relative {{ empty($field['value']) ? 'hidden' : '' }} border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50 dark:bg-gray-950 p-4 flex items-center justify-center overflow-hidden" style="height: 160px; max-width: 320px;">
                                                    <img src="{{ $field['value'] ?? '' }}" id="{{ $field['name'] }}-preview-img" alt="{{ $field['label'] }} Preview"
                                                        class="max-h-full max-w-full object-contain rounded-lg shadow-sm">
                                                </div>
                                            </div>

                                        <!-- Render TEXTAREA fields -->
                                        @elseif ($field['type'] === 'textarea')
                                            <textarea name="{{ $field['name'] }}" rows="4"
                                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                                class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                                @if(!empty($field['required'])) required @endif>{{ $field['value'] ?? '' }}</textarea>

                                        <!-- Render COLOR pickers -->
                                        @elseif ($field['type'] === 'color')
                                            <div class="flex items-center gap-3">
                                                <div class="w-11 h-11 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden relative shadow-theme-xs">
                                                    <input type="color" name="{{ $field['name'] }}" value="{{ $field['value'] ?? '#000000' }}"
                                                        class="absolute -inset-2 cursor-pointer w-[200%] h-[200%]"
                                                        oninput="document.getElementById('{{ $field['name'] }}-text-val').value = this.value">
                                                </div>
                                                <input type="text" id="{{ $field['name'] }}-text-val" value="{{ $field['value'] ?? '#000000' }}"
                                                    oninput="document.querySelector('input[name={{ $field['name'] }}]').value = this.value"
                                                    class="h-11 w-32 rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs">
                                            </div>

                                        <!-- Render generic TEXT inputs -->
                                        @else
                                            <input type="text" name="{{ $field['name'] }}" value="{{ $field['value'] ?? '' }}"
                                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                                class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                                @if(!empty($field['required'])) required @endif>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('merchant.settings.themes') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold text-white transition-all active:scale-95">
                            Save Theme Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripting for live image previews and secure AJAX submission -->
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

        document.getElementById('theme-settings-form').onsubmit = async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Saving...';

            try {
                const formData = new FormData(this);
                const response = await fetch("{{ route('merchant.settings.themes.manage.update', $slug) }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (data.status === 'true') {
                    showToast('success', 'Theme configurations updated successfully!');
                } else {
                    showToast('error', data.message || 'Saving configuration failed.');
                }
            } catch (error) {
                showToast('error', 'Network error. Please try again.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        };
    </script>
@endsection
