@extends('merchant.default.layouts.app')

@section('title', 'Social Profiles')

@section('content')
    <div id="settings-container">
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `Social Profiles` }">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">Social Profiles</h2>
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
                            <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">Social Profiles</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Breadcrumb End -->

            <div class="max-w-4xl">
                <form id="social-settings-form" class="space-y-6">
                    @csrf

                    <div
                        class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs overflow-hidden">
                        <div
                            class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-transparent">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Connection Links</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Configure how customers reach you on
                                    social platforms.</p>
                            </div>
                        </div>

                        <div class="p-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- WhatsApp -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">WhatsApp
                                    Number</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.937 3.659 1.432 5.631 1.432h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.415-8.411" />
                                        </svg>
                                    </div>
                                    <input type="text" name="whatsapp_number" value="{{ $brand->whatsapp_number }}"
                                        class="h-11 w-full rounded-lg border border-gray-200 bg-white pl-11 pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs transition-colors"
                                        placeholder="+1234567890">
                                </div>
                            </div>

                            <!-- Telegram -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Telegram</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M11.944 0C5.352 0 0 5.352 0 11.944s5.352 11.944 11.944 11.944 11.944-5.352 11.944-11.944S18.536 0 11.944 0zM17.41 8.082l-1.871 8.815c-.141.621-.508.775-1.028.484l-2.844-2.098-1.373 1.321c-.153.153-.281.281-.576.281l.204-2.903 5.284-4.774c.23-.204-.05-.317-.357-.113L8.33 13.33l-2.813-.878c-.611-.191-.623-.611.127-.903l10.989-4.237c.509-.185.954.12.777.77z" />
                                        </svg>
                                    </div>
                                    <input type="text" name="telegram" value="{{ $brand->telegram }}"
                                        class="h-11 w-full rounded-lg border border-gray-200 bg-white pl-11 pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs transition-colors"
                                        placeholder="username or link">
                                </div>
                            </div>

                            <!-- Messenger -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Facebook
                                    Messenger</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.59 6.621 4.093 8.665.215.176.342.434.342.712v2.525c0 .546.592.89 1.066.618l2.824-1.616a.916.916 0 0 1 .536-.145c.373.045.753.07 1.139.07 6.627 0 12-4.974 12-11.111C24 4.974 18.627 0 12 0zm1.325 14.864l-2.383-2.544-4.654 2.544 5.118-5.438 2.434 2.544 4.603-2.544-5.118 5.438z" />
                                        </svg>
                                    </div>
                                    <input type="text" name="facebook_messenger" value="{{ $brand->facebook_messenger }}"
                                        class="h-11 w-full rounded-lg border border-gray-200 bg-white pl-11 pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs transition-colors"
                                        placeholder="username or link">
                                </div>
                            </div>

                            <!-- Facebook Page -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Facebook Page
                                    URL</label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                        </svg>
                                    </div>
                                    <input type="url" name="facebook_page" value="{{ $brand->facebook_page }}"
                                        class="h-11 w-full rounded-lg border border-gray-200 bg-white pl-11 pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs transition-colors"
                                        placeholder="https://facebook.com/yourpage">
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex justify-end p-6 bg-gray-50/50 dark:bg-gray-800/20 border-t border-gray-100 dark:border-gray-800">
                            <button type="submit"
                                class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-10 py-3 text-sm font-bold text-white transition-all active:scale-95">
                                Save Social Profiles
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.getElementById('social-settings-form').onsubmit = async function(e) {
                e.preventDefault();
                const btn = this.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML =
                    '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Saving...';

                try {
                    const formData = new FormData(this);
                    const response = await fetch("{{ route('merchant.settings.social.update') }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    if (data.status === 'success') {
                        showToast('success', 'Social profiles updated successfully');
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
