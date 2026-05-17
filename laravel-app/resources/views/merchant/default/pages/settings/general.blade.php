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
                                    <div x-data="{ switcherToggle: {{ $brand->auto_exchange ? 'true' : 'false' }} }">
                                        <label for="auto_exchange" class="relative inline-block cursor-pointer">
                                            <input type="checkbox" id="auto_exchange" name="auto_exchange" value="1"
                                                class="sr-only" x-model="switcherToggle">
                                            <div class="block h-6 w-11 rounded-full transition-colors duration-200"
                                                :class="switcherToggle ? 'bg-brand-500' : 'bg-gray-200 dark:bg-white/10'">
                                            </div>
                                            <div :class="switcherToggle ? 'translate-x-full' : 'translate-x-0'"
                                                class="shadow-theme-sm absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white duration-200 ease-linear transform">
                                            </div>
                                        </label>
                                    </div>
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

                {{-- Social Profiles Section Start --}}
                <div
                    class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Social Profiles</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Configure how customers reach you on social platforms.</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <!-- WhatsApp -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">WhatsApp Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.937 3.659 1.432 5.631 1.432h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.415-8.411" />
                                        </svg>
                                    </div>
                                    <input type="text" name="whatsapp_number" value="{{ $brand->whatsapp_number }}"
                                        class="h-11 w-full rounded-lg border border-gray-200 bg-white pl-11 pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs transition-colors"
                                        placeholder="+1234567890">
                                </div>
                            </div>

                            <!-- Telegram -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Telegram</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M11.944 0C5.352 0 0 5.352 0 11.944s5.352 11.944 11.944 11.944 11.944-5.352 11.944-11.944S18.536 0 11.944 0zM17.41 8.082l-1.871 8.815c-.141.621-.508.775-1.028.484l-2.844-2.098-1.373 1.321c-.153.153-.281.281-.576.281l.204-2.903 5.284-4.774c.23-.204-.05-.317-.357-.113L8.33 13.33l-2.813-.878c-.611-.191-.623-.611.127-.903l10.989-4.237c.509-.185.954.12.777.77z" />
                                        </svg>
                                    </div>
                                    <input type="text" name="telegram" value="{{ $brand->telegram }}"
                                        class="h-11 w-full rounded-lg border border-gray-200 bg-white pl-11 pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs transition-colors"
                                        placeholder="username or link">
                                </div>
                            </div>

                            <!-- Messenger -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Facebook Messenger</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.59 6.621 4.093 8.665.215.176.342.434.342.712v2.525c0 .546.592.89 1.066.618l2.824-1.616a.916.916 0 0 1 .536-.145c.373.045.753.07 1.139.07 6.627 0 12-4.974 12-11.111C24 4.974 18.627 0 12 0zm1.325 14.864l-2.383-2.544-4.654 2.544 5.118-5.438 2.434 2.544 4.603-2.544-5.118 5.438z" />
                                        </svg>
                                    </div>
                                    <input type="text" name="facebook_messenger" value="{{ $brand->facebook_messenger }}"
                                        class="h-11 w-full rounded-lg border border-gray-200 bg-white pl-11 pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs transition-colors"
                                        placeholder="username or link">
                                </div>
                            </div>

                            <!-- Facebook Page -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Facebook Page URL</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                        </svg>
                                    </div>
                                    <input type="url" name="facebook_page" value="{{ $brand->facebook_page }}"
                                        class="h-11 w-full rounded-lg border border-gray-200 bg-white pl-11 pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs transition-colors"
                                        placeholder="https://facebook.com/yourpage">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Social Profiles Section End --}}

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
