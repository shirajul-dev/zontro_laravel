@extends('merchant.default.layouts.app')

@section('title', 'Manage System')

@section('content')
<div class="p-4 md:p-6 lg:p-10">
    <!-- Settings Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">Manage System</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-medium">Manage and configure your integrated system engines, payment channels, and processing gateways.</p>
    </div>

    <!-- Settings Grid -->
    <div id="settings-container">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-0 border-gray-100 dark:border-gray-800 overflow-hidden bg-white dark:bg-gray-900 border">

            <x-settings-card
                href="{{ route('merchant.system.gateways') }}"
                title="Payment Gateways"
                data-nav-link
                description="Manage active payment settlement MFS engines, automated global gateways, or customize manual bank transfers.">
                <x-slot:icon>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </x-slot:icon>
            </x-settings-card>

        </div>
    </div>
</div>
@endsection
