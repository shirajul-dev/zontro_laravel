@extends('merchant.default.layouts.app')

@section('title', 'Configure Gateway: ' . $gateway->name)

@section('content')
    <div id="settings-container">
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: 'Configure {{ $gateway->name }}' }">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">Configure Gateway</h2>
                    <nav>
                        <ol class="flex items-center gap-1.5">
                            <li>
                                <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                                    href="{{ route('merchant.system') }}">
                                    Manage System
                                    <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </a>
                            </li>
                            <li>
                                <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                                    href="{{ route('merchant.system.gateways') }}">
                                    Payment Gateways
                                    <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </a>
                            </li>
                            <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">Configure Gateway</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Breadcrumb End -->

            <form id="gateway-settings-form" class="space-y-6 max-w-4xl" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="gateway-id" value="{{ $gateway->gateway_id }}">

                <div class="max-w-4xl space-y-6">
                    <!-- General settings card -->
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">General Information</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Configure the gateway identity, active status, and settlement currency.</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="col-span-full">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Gateway Engine</label>
                                    <input type="text" value="{{ $gateway->name }}" disabled
                                        class="h-11 w-full rounded-lg border border-gray-200 bg-gray-50 dark:bg-gray-800/40 px-4 text-sm font-medium text-gray-500 shadow-theme-xs">
                                </div>
                                
                                <div class="col-span-full">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Display Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="display_name" value="{{ $gateway->display }}" required placeholder="Enter customer display label"
                                        class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs">
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Settlement Currency <span class="text-red-500">*</span></label>
                                    <div class="relative z-20">
                                        <select name="currency" id="gateway-currency-select" onchange="updateCurrencySymbols()"
                                            class="h-11 w-full appearance-none rounded-lg border border-gray-200 bg-transparent px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:text-white/90 shadow-theme-xs">
                                            @foreach($brandCurrencies as $curr)
                                                <option value="{{ $curr->code }}" {{ $gateway->currency === $curr->code ? 'selected' : '' }}>
                                                    {{ $curr->code }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                            <svg class="stroke-current" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Status <span class="text-red-500">*</span></label>
                                    <div class="relative z-20">
                                        <select name="status"
                                            class="h-11 w-full appearance-none rounded-lg border border-gray-200 bg-transparent px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:text-white/90 shadow-theme-xs">
                                            <option value="active" {{ $gateway->status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $gateway->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                            <svg class="stroke-current" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Limits card -->
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Transaction Limits</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Define minimum and maximum settlement bounds for transaction routing.</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Min Amount <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute top-1/2 left-4 -translate-y-1/2 text-sm font-bold text-gray-400 currency-addon-label">{{ $gateway->currency }}</span>
                                        <input type="text" name="min_amount" value="{{ number_format((float)$gateway->min_allow, 2, '.', '') }}" required
                                            class="h-11 w-full rounded-lg border border-gray-200 bg-white pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                            style="padding-left: 60px;">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Max Amount <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute top-1/2 left-4 -translate-y-1/2 text-sm font-bold text-gray-400 currency-addon-label">{{ $gateway->currency }}</span>
                                        <input type="text" name="max_amount" value="{{ number_format((float)$gateway->max_allow, 2, '.', '') }}" required
                                            class="h-11 w-full rounded-lg border border-gray-200 bg-white pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                            style="padding-left: 60px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payout charges card -->
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Charges & Discounts</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Configure processing charges and customer incentives applied to settlements.</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Fixed Charge <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute top-1/2 left-4 -translate-y-1/2 text-sm font-bold text-gray-400 currency-addon-label">{{ $gateway->currency }}</span>
                                        <input type="text" name="fixed_charge" value="{{ number_format((float)$gateway->fixed_charge, 2, '.', '') }}" required
                                            class="h-11 w-full rounded-lg border border-gray-200 bg-white pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                            style="padding-left: 60px;">
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Percentage Charge <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute top-1/2 left-4 -translate-y-1/2 text-sm font-bold text-gray-400">%</span>
                                        <input type="text" name="percentage_charge" value="{{ number_format((float)$gateway->percentage_charge, 2, '.', '') }}" required
                                            class="h-11 w-full rounded-lg border border-gray-200 bg-white pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                            style="padding-left: 36px;">
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Fixed Discount <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute top-1/2 left-4 -translate-y-1/2 text-sm font-bold text-gray-400 currency-addon-label">{{ $gateway->currency }}</span>
                                        <input type="text" name="fixed_discount" value="{{ number_format((float)$gateway->fixed_discount, 2, '.', '') }}" required
                                            class="h-11 w-full rounded-lg border border-gray-200 bg-white pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                            style="padding-left: 60px;">
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Percentage Discount <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="absolute top-1/2 left-4 -translate-y-1/2 text-sm font-bold text-gray-400">%</span>
                                        <input type="text" name="percentage_discount" value="{{ number_format((float)$gateway->percentage_discount, 2, '.', '') }}" required
                                            class="h-11 w-full rounded-lg border border-gray-200 bg-white pr-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                            style="padding-left: 36px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Override Branding card -->
                    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Visual Customizations</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Design custom buttons, colors, and specific branding overrides for this channel.</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Primary Color <span class="text-red-500">*</span></label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden relative shadow-theme-xs">
                                            <input type="color" name="primary_color" value="{{ $gateway->primary_color ?? '#1e293b' }}"
                                                class="absolute -inset-2 cursor-pointer w-[200%] h-[200%]"
                                                oninput="document.getElementById('primary_color-text').value = this.value">
                                        </div>
                                        <input type="text" id="primary_color-text" value="{{ $gateway->primary_color ?? '#1e293b' }}"
                                            oninput="document.querySelector('input[name=primary_color]').value = this.value"
                                            class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs">
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Text Color <span class="text-red-500">*</span></label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden relative shadow-theme-xs">
                                            <input type="color" name="text_color" value="{{ $gateway->text_color ?? '#ffffff' }}"
                                                class="absolute -inset-2 cursor-pointer w-[200%] h-[200%]"
                                                oninput="document.getElementById('text_color-text').value = this.value">
                                        </div>
                                        <input type="text" id="text_color-text" value="{{ $gateway->text_color ?? '#ffffff' }}"
                                            oninput="document.querySelector('input[name=text_color]').value = this.value"
                                            class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs">
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Button Color <span class="text-red-500">*</span></label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden relative shadow-theme-xs">
                                            <input type="color" name="btn_color" value="{{ $gateway->btn_color ?? '#3b82f6' }}"
                                                class="absolute -inset-2 cursor-pointer w-[200%] h-[200%]"
                                                oninput="document.getElementById('btn_color-text').value = this.value">
                                        </div>
                                        <input type="text" id="btn_color-text" value="{{ $gateway->btn_color ?? '#3b82f6' }}"
                                            oninput="document.querySelector('input[name=btn_color]').value = this.value"
                                            class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs">
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Button Text <span class="text-red-500">*</span></label>
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden relative shadow-theme-xs">
                                            <input type="color" name="btn_text_color" value="{{ $gateway->btn_text_color ?? '#ffffff' }}"
                                                class="absolute -inset-2 cursor-pointer w-[200%] h-[200%]"
                                                oninput="document.getElementById('btn_text_color-text').value = this.value">
                                        </div>
                                        <input type="text" id="btn_text_color-text" value="{{ $gateway->btn_text_color ?? '#ffffff' }}"
                                            oninput="document.querySelector('input[name=btn_text_color]').value = this.value"
                                            class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs">
                                    </div>
                                </div>

                                <div class="col-span-full">
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Custom Gateway Logo</label>
                                    <div class="space-y-3">
                                        <div class="relative flex items-center justify-between border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-lg p-3 shadow-theme-xs">
                                            <input type="file" name="gateway_logo" id="gateway_logo"
                                                class="absolute inset-0 opacity-0 cursor-pointer z-10"
                                                onchange="previewGatewayLogo(this)">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300" id="logo-filename-label">Choose custom logo...</p>
                                                    <p class="text-[10px] text-gray-400 font-medium">PNG, JPG, JPEG up to 2MB (500x250 recommended)</p>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center justify-center rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                Browse
                                            </span>
                                        </div>
                                        
                                        <div id="logo-preview-box" class="relative border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50 dark:bg-gray-950 p-4 flex items-center justify-center overflow-hidden" style="height: 140px; max-width: 320px;">
                                            <img src="{{ !empty($gateway->logo) && $gateway->logo !== '--' ? $gateway->logo : asset('assets/images/logo-light.png') }}" 
                                                id="logo-preview-img" alt="Logo Preview"
                                                class="max-h-full max-w-full object-contain rounded-lg shadow-sm">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic credentials card -->
                    @if(!empty($fields))
                        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">API Credentials & Setup</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Provide required secure keys, modes, and parameters for transactional validation.</p>
                                </div>
                            </div>
                            <div class="p-6 space-y-4">
                                @foreach($fields as $field)
                                    @php
                                        $val = $parameters[$field['name']] ?? '';
                                        if ($val === '--') $val = '';
                                    @endphp
                                    <div class="space-y-1.5">
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                            {{ $field['label'] }}
                                            @if(!empty($field['required'])) <span class="text-red-500">*</span> @endif
                                        </label>
                                        
                                        @if($field['type'] === 'select')
                                            <div class="relative z-20">
                                                <select name="{{ $field['name'] }}" 
                                                    class="h-11 w-full appearance-none rounded-lg border border-gray-200 bg-transparent px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:text-white/90 shadow-theme-xs"
                                                    @if(!empty($field['required'])) required @endif>
                                                    @foreach($field['options'] as $k => $v)
                                                        <option value="{{ $k }}" {{ $val === $k ? 'selected' : '' }}>{{ $v }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500">
                                                    <svg class="stroke-current" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                        @elseif($field['type'] === 'checkbox')
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="{{ $field['name'] }}" value="1" {{ $val == '1' ? 'checked' : '' }}
                                                    class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-brand-500/20 dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-500"></div>
                                            </label>
                                        @elseif($field['type'] === 'textarea')
                                            <textarea name="{{ $field['name'] }}" rows="4" placeholder="{{ $field['placeholder'] ?? '' }}"
                                                class="w-full rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                                @if(!empty($field['required'])) required @endif>{{ $val }}</textarea>
                                        @else
                                            <input type="text" name="{{ $field['name'] }}" value="{{ $val }}" placeholder="{{ $field['placeholder'] ?? '' }}"
                                                class="h-11 w-full rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 focus:border-brand-500 focus:outline-hidden dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 shadow-theme-xs"
                                                @if(!empty($field['required'])) required @endif>
                                        @endif

                                        @if(!empty($field['hint']))
                                            <p class="text-xs text-gray-400 font-medium">{{ $field['hint'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- IPN Webhook helper panel -->
                    @if($gateway->slug !== 'bank-transfer' && $gateway->tab !== 'bank')
                        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Instant Payment Notification (IPN)</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Configure callback endpoints inside your provider portal to receive real-time updates.</p>
                                </div>
                            </div>
                            <div class="p-6 space-y-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                                    Configure this secure callback URL inside your payment provider portal to automatically sync order settlement states.
                                </p>
                                <div class="relative flex items-center">
                                    <input type="text" id="ipn-callback-url-input" readonly value="{{ url('/ipn/' . $gateway->gateway_id) }}"
                                        class="h-11 w-full rounded-lg border border-gray-200 bg-gray-50 dark:bg-gray-800/40 pl-4 pr-12 text-xs font-semibold text-gray-600 dark:text-gray-300 shadow-theme-xs select-all">
                                    <button type="button" onclick="copyIpnUrl()" class="absolute right-3 text-gray-400 hover:text-brand-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Supported Languages card -->
                    @if(!empty($supportedLanguages))
                        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 11.37 7.361 16.5 3 18.067"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Supported Languages</h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">View languages natively supported for checkout display by this gateway provider.</p>
                                </div>
                            </div>
                            <div class="p-6 flex flex-wrap gap-1.5">
                                @foreach($supportedLanguages as $langCode => $langName)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 border border-brand-100/50 dark:border-brand-500/10">
                                        {{ $langName }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Form Bottom Actions Outside of Card -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route('merchant.system.gateways') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-6 py-3 text-sm font-semibold text-white transition-all active:scale-95">
                        Save Configurations
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Live Update currency addons dynamically
            function updateCurrencySymbols() {
                const currency = document.getElementById('gateway-currency-select').value;
                document.querySelectorAll('.currency-addon-label').forEach(el => {
                    el.textContent = currency;
                });
            }

            // Preview Logo Upload File
            function previewGatewayLogo(input) {
                const file = input.files[0];
                if (file) {
                    document.getElementById('logo-filename-label').textContent = file.name;
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('logo-preview-img').src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            }

            // Click Copy Webhook URL
            function copyIpnUrl() {
                const input = document.getElementById('ipn-callback-url-input');
                input.select();
                input.setSelectionRange(0, 99999);
                document.execCommand('copy');
                
                showToast('success', 'Gateway IPN webhook address copied successfully.');
            }

            // Save Configurations form trigger
            document.getElementById('gateway-settings-form').onsubmit = async function(e) {
                e.preventDefault();
                const btn = this.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Saving...';

                try {
                    const formData = new FormData(this);
                    const response = await fetch("{{ route('merchant.system.gateways.update', $gateway->gateway_id) }}", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    if (data.status === 'true') {
                        showToast('success', data.message || 'Payment gateway settings updated successfully!');
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
    @endpush
@endsection
