@extends('merchant.default.layouts.app')

@section('title', 'Brand & Logos')

@section('content')
<div class="p-4 md:p-6 lg:p-10">
    <div class="mb-8 flex items-center gap-4">
        <a href="{{ route('merchant.settings') }}" class="group flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:border-brand-500 hover:text-brand-500 transition-all dark:border-gray-800 dark:bg-gray-900">
            <svg class="h-5 w-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">Brand & Logos</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-medium">Customize your brand's visual identity.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 md:p-8 shadow-sm">
        @include('m::pages.settings.sections.branding')
    </div>
</div>
@endsection
