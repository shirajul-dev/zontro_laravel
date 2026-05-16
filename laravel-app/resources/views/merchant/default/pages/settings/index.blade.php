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
                href="{{ route('merchant.settings.social') }}"
                title="Social Profiles"
                data-nav-link
                description="Connect your Telegram, WhatsApp, and Facebook profiles to improve customer reach.">
                <x-slot:icon>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </x-slot:icon>
            </x-settings-card>
        </div>
    </div>
</div>
@endsection
