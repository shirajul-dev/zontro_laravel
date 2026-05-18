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
                <form id="theme-settings-form" class="space-y-6" enctype="multipart/form-data">
                    @csrf

                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
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
                        <div class="p-6 space-y-6">
                            @foreach ($fields as $field)
                                <div class="space-y-2">
                                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $field['label'] }}
                                        @if (!empty($field['required']))
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>

                                    <!-- Render SELECT drop list -->
                                    @if ($field['type'] === 'select')
                                        <div class="relative z-20 bg-transparent dark:bg-gray-900">
                                            <select name="{{ $field['name'] }}" 
                                                class="relative z-20 w-full appearance-none rounded-lg border border-gray-200 bg-transparent px-5 py-3 text-sm text-gray-800 outline-none transition focus:border-brand-500 active:border-brand-500 dark:border-gray-800 dark:bg-transparent dark:text-white dark:focus:border-brand-500"
                                                @if(!empty($field['required'])) required @endif>
                                                @foreach ($field['options'] as $optVal => $optLabel)
                                                    <option value="{{ $optVal }}" {{ ($field['value'] ?? '') === $optVal ? 'selected' : '' }} class="dark:bg-gray-900">
                                                        {{ $optLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="absolute right-4 top-1/2 z-30 -translate-y-1/2">
                                                <svg class="fill-current text-gray-500" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M5.29289 8.29289C5.68342 7.90237 6.31658 7.90237 6.70711 8.29289L12 13.5858L17.2929 8.29289C17.6834 7.90237 18.3166 7.90237 18.7071 8.29289C19.0976 8.68342 19.0976 9.31658 18.7071 9.70711L12.7071 15.7071C12.3166 16.0976 11.6834 16.0976 11.2929 15.7071L5.29289 9.70711C4.90237 9.31658 4.90237 8.68342 5.29289 8.29289Z" fill=""></path>
                                                </svg>
                                            </span>
                                        </div>

                                    <!-- Render IMAGE upload zone -->
                                    @elseif ($field['type'] === 'image')
                                        <div class="relative group">
                                            <div id="{{ $field['name'] }}-preview-container"
                                                class="flex items-center justify-center w-full h-48 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50 dark:bg-gray-900/50 dark:border-gray-800 group-hover:border-brand-500 transition-all cursor-pointer overflow-hidden shadow-inner">
                                                @if (!empty($field['value']))
                                                    <img src="{{ $field['value'] }}" id="{{ $field['name'] }}-preview-img" alt="{{ $field['label'] }}"
                                                        class="max-h-full max-w-full p-4 object-contain transition-transform group-hover:scale-105">
                                                @else
                                                    <div class="text-center space-y-2" id="{{ $field['name'] }}-placeholder">
                                                        <div class="flex justify-center">
                                                            <div class="p-3 rounded-full bg-white dark:bg-gray-800 shadow-sm text-gray-400">
                                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                        <p class="text-xs font-medium text-gray-500">Upload {{ $field['label'] }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <input type="file" name="{{ $field['name'] }}"
                                                class="absolute inset-0 opacity-0 cursor-pointer"
                                                onchange="previewImage(this, '{{ $field['name'] }}-preview-img', '{{ $field['name'] }}-placeholder')">
                                        </div>

                                    <!-- Render TEXTAREA fields -->
                                    @elseif ($field['type'] === 'textarea')
                                        <textarea name="{{ $field['name'] }}" rows="4"
                                            placeholder="{{ $field['placeholder'] ?? '' }}"
                                            class="w-full rounded-lg border border-gray-200 bg-transparent px-5 py-3 text-sm text-gray-800 outline-none transition focus:border-brand-500 active:border-brand-500 dark:border-gray-800 dark:bg-transparent dark:text-white dark:focus:border-brand-500"
                                            @if(!empty($field['required'])) required @endif>{{ $field['value'] ?? '' }}</textarea>

                                    <!-- Render COLOR pickers -->
                                    @elseif ($field['type'] === 'color')
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden relative shadow-sm">
                                                <input type="color" name="{{ $field['name'] }}" value="{{ $field['value'] ?? '#000000' }}"
                                                    class="absolute -inset-2 cursor-pointer w-[200%] h-[200%]"
                                                    oninput="document.getElementById('{{ $field['name'] }}-text-val').value = this.value">
                                            </div>
                                            <input type="text" id="{{ $field['name'] }}-text-val" value="{{ $field['value'] ?? '#000000' }}"
                                                oninput="document.querySelector('input[name={{ $field['name'] }}]').value = this.value"
                                                class="w-32 rounded-lg border border-gray-200 bg-transparent px-4 py-2.5 text-sm text-gray-800 outline-none transition focus:border-brand-500 active:border-brand-500 dark:border-gray-800 dark:bg-transparent dark:text-white dark:focus:border-brand-500">
                                        </div>

                                    <!-- Render generic TEXT inputs -->
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" value="{{ $field['value'] ?? '' }}"
                                            placeholder="{{ $field['placeholder'] ?? '' }}"
                                            class="w-full rounded-lg border border-gray-200 bg-transparent px-5 py-3 text-sm text-gray-800 outline-none transition focus:border-brand-500 active:border-brand-500 dark:border-gray-800 dark:bg-transparent dark:text-white dark:focus:border-brand-500"
                                            @if(!empty($field['required'])) required @endif>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Card Footer Update Controls -->
                        <div class="flex justify-end p-6 bg-gray-50/50 dark:bg-gray-800/20 border-t border-gray-100 dark:border-gray-800">
                            <button type="submit"
                                class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-10 py-3 text-sm font-bold text-white transition-all active:scale-95">
                                Save Theme Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripting for live image previews and secure AJAX submission -->
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
                        img.className = 'max-h-full max-w-full p-4 object-contain transition-transform group-hover:scale-105';
                        input.parentElement.querySelector('div').appendChild(img);
                    }

                    img.src = e.target.result;
                    img.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
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
