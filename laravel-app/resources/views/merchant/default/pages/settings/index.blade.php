@extends('merchant.default.layouts.app')

@section('title', 'Settings')

@section('content')
<div class="p-4 md:p-6 lg:p-10">
    <!-- Settings Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">Brand Settings</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-medium">Manage your brand configurations, social profiles, and integration keys.</p>
    </div>

    <!-- Settings Grid -->
    <div id="settings-container">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-0 border-gray-100 dark:border-gray-800 overflow-hidden bg-white dark:bg-gray-900 border">

            <x-settings-card
                href="{{ route('merchant.settings.general') }}"
                title="General Settings"
                data-nav-link
                description="Configure your basic brand identity, currency, timezone, and language preferences.">
                <x-slot:icon>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </x-slot:icon>
            </x-settings-card>

            <!-- Other cards remain same for now, just adding data-nav-link -->
            <x-settings-card
                href="{{ route('merchant.settings.branding') }}"
                title="Brand & Logos"
                data-nav-link
                description="Upload and manage your brand assets including logos, favicons, and dark mode variations.">
                <x-slot:icon>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </x-slot:icon>
            </x-settings-card>

            <x-settings-card
                href="{{ route('merchant.settings.faqs') }}"
                title="FAQ Settings"
                data-nav-link
                description="Configure Frequently Asked Questions (FAQs) to display on your checkout page to resolve customer doubts.">
                <x-slot:icon>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </x-slot:icon>
            </x-settings-card>

            <x-settings-card
                href="{{ route('merchant.settings.currencies') }}"
                title="Currencies"
                data-nav-link
                description="Manage your brand currencies, import new ones, and sync exchange rates automatically.">
                <x-slot:icon>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </x-slot:icon>
            </x-settings-card>

            <x-settings-card
                href="{{ route('merchant.settings.api-keys') }}"
                title="API Credentials"
                data-nav-link
                description="Manage your API keys, sandbox credentials, and access scopes for third-party checkout integrations.">
                <x-slot:icon>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-9-3h.01M5.071 19.071c1.758-1.758 4.29-2.012 6.315-.815l7.15-7.15a3 3 0 114.243 4.243l-7.15 7.15c-1.197 2.025-1.451 4.557.307 6.315a6.002 6.002 0 01-8.544 0 6.002 6.002 0 010-8.544z"></path>
                    </svg>
                </x-slot:icon>
            </x-settings-card>

            <x-settings-card
                href="{{ route('merchant.settings.domains') }}"
                title="Whitelisted Domains"
                data-nav-link
                description="Whitelist target checkout domains to prevent unauthorized API requests and secure hosted checkout redirection.">
                <x-slot:icon>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                    </svg>
                </x-slot:icon>
            </x-settings-card>
        </div>
    </div>
</div>
@endsection
