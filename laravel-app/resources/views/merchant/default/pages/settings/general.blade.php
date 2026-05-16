@extends('merchant.default.layouts.app')

@section('title', 'General Settings')

@section('content')
    <div id="settings-container">
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `General Settings` }">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">General Settings</h2>
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
                            <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">General Settings</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Breadcrumb End -->

            <!-- Content Start -->
            <form id="general-settings-form" method="POST" class="space-y-6 max-w-4xl">
                @csrf

                {{-- Basic Information Section Start --}}
                <div
                    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-7h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Basic Information</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Configure your brand identity and
                                localization.</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="col-span-full">
                                <label for="name"
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Brand Name <span class="text-error-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" value="{{ $brand->name }}" required
                                    class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                    placeholder="Enter your brand name">
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Default Timezone <span class="text-error-500">*</span>
                                </label>
                                <div class="relative z-20">
                                    <select name="timezone"
                                        class="h-11 w-full appearance-none rounded-lg border border-gray-200 bg-transparent px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:text-white/90 shadow-theme-xs">
                                        @foreach ($timezones as $tz)
                                            <option value="{{ $tz }}"
                                                {{ $brand->timezone == $tz ? 'selected' : '' }}>
                                                {{ $tz }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span
                                        class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                        <svg class="stroke-current" width="18" height="18" viewBox="0 0 20 20"
                                            fill="none">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Default Language <span class="text-error-500">*</span>
                                </label>
                                <div class="relative z-20">
                                    <select name="language"
                                        class="h-11 w-full appearance-none rounded-lg border border-gray-200 bg-transparent px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:text-white/90 shadow-theme-xs">
                                        @foreach ($languages as $code => $label)
                                            <option value="{{ $code }}"
                                                {{ $brand->language == $code ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span
                                        class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                        <svg class="stroke-current" width="18" height="18" viewBox="0 0 20 20"
                                            fill="none">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Default Currency <span class="text-error-500">*</span>
                                </label>
                                <div class="relative z-20">
                                    <select name="currency_code" id="currency_code_select"
                                        class="h-11 w-full appearance-none rounded-lg border border-gray-200 bg-transparent px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:text-white/90 shadow-theme-xs">
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->code }}"
                                                {{ $brand->currency_code == $currency->code ? 'selected' : '' }}>
                                                {{ $currency->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span
                                        class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                        <svg class="stroke-current" width="18" height="18" viewBox="0 0 20 20"
                                            fill="none">
                                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Max Payment Tolerance
                                </label>
                                <div class="relative">
                                    <span id="tolerance-currency-prefix"
                                        class="absolute top-1/2 left-4 -translate-y-1/2 text-sm font-bold text-gray-400">
                                        {{ $brand->currency_code }}
                                    </span>
                                    <input type="number" step="0.01" name="payment_tolerance"
                                        value="{{ $brand->payment_tolerance }}"
                                        class="h-11 w-full rounded-lg border border-gray-200 bg-white pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                        style="padding-left: 60px;">
                                </div>
                            </div>

                            <div class="col-span-full pt-2">
                                <div
                                    class="flex items-center justify-between gap-4 p-4 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/20">
                                    <div class="space-y-0.5">
                                        <h4 class="text-sm font-semibold text-gray-800 dark:text-white">Automatic Exchange
                                            Rates</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Automatically sync exchange
                                            rates from global providers daily.</p>
                                    </div>
                                    <label class="relative inline-flex cursor-pointer items-center">
                                        <input type="checkbox" name="auto_exchange" value="1" class="sr-only peer"
                                            {{ $brand->auto_exchange ? 'checked' : '' }}>
                                        <div
                                            class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-brand-500 peer-checked:after:translate-x-full peer-checked:after:border-white dark:border-gray-600 dark:bg-gray-700">
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Basic Information Section End --}}

                {{-- Business Details Section Start --}}
                <div
                    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Business Location</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Update your physical business address
                                details.</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div class="col-span-full">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Street
                                    Address</label>
                                <input type="text" name="street_address" value="{{ $brand->street_address }}"
                                    class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                    placeholder="Enter street address">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">City /
                                    Town</label>
                                <input type="text" name="city_town" value="{{ $brand->city_town }}"
                                    class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                    placeholder="Enter city">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Postal
                                    Code</label>
                                <input type="text" name="postal_code" value="{{ $brand->postal_code }}"
                                    class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                    placeholder="Enter postal code">
                            </div>
                            <div class="col-span-full">
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Country</label>
                                <input type="text" name="country" value="{{ $brand->country }}"
                                    class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                    placeholder="Enter country name">
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Business Details Section End --}}

                {{-- Support Contact Information Section Start --}}
                <div
                    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Support Channels</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">How your customers can reach you.</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Support
                                    Email</label>
                                <input type="email" name="support_email" value="{{ $brand->support_email }}"
                                    class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                    placeholder="support@brand.com">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Support
                                    Phone</label>
                                <input type="text" name="support_phone" value="{{ $brand->support_phone }}"
                                    class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                    placeholder="+1234567890">
                            </div>
                            <div class="col-span-full">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Support
                                    Website</label>
                                <input type="url" name="support_website" value="{{ $brand->support_website }}"
                                    class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                    placeholder="https://brand.com/support">
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Support Contact Information Section End --}}

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('merchant.settings') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                        Save Changes
                    </button>
                </div>
            </form>
            <!-- Content End -->
        </div>

        <script>
            (function() {
                // Update currency prefix dynamic logic
                const currencySelect = document.getElementById('currency_code_select');
                const currencyPrefix = document.getElementById('tolerance-currency-prefix');

                if (currencySelect && currencyPrefix) {
                    currencySelect.addEventListener('change', (e) => {
                        currencyPrefix.textContent = e.target.value;
                    });
                }

                document.getElementById('general-settings-form').onsubmit = async function(e) {
                    e.preventDefault();
                    const btn = this.querySelector('button[type="submit"]');
                    const originalBtnContent = btn.innerHTML;

                    btn.disabled = true;
                    btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Saving...
            `;

                    try {
                        const formData = new FormData(this);
                        // Handle checkbox manually if not checked
                        if (!formData.has('auto_exchange')) {
                            formData.append('auto_exchange', '0');
                        }

                        const response = await fetch("{{ route('merchant.settings.general.update') }}", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const result = await response.json();

                        if (response.ok && result.status === 'success') {
                            showToast('success', result.message || 'Settings updated successfully');
                        } else {
                            showToast('error', result.message || 'Failed to update settings');
                        }
                    } catch (error) {
                        showToast('error', 'Network error. Please try again.');
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = originalBtnContent;
                    }
                };
            })();
        </script>

    </div>
@endsection
