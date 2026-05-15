@extends('m::layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 md:gap-6">
        <!-- Stat Card 1 -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-500 dark:bg-brand-500/10">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="flex items-center gap-1 text-xs font-medium text-success-500 bg-success-50 dark:bg-success-500/10 px-2 py-1 rounded-full">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <path d="M18 15l-6-6-6 6"/>
                    </svg>
                    12.5%
                </span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Balance</p>
                <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">$45,285.00</h4>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-success-50 text-success-500 dark:bg-success-500/10">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" stroke="currentColor" stroke-width="2"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96" stroke="currentColor" stroke-width="2"/>
                        <line x1="12" y1="22.08" x2="12" y2="12" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </div>
                <span class="flex items-center gap-1 text-xs font-medium text-success-500 bg-success-50 dark:bg-success-500/10 px-2 py-1 rounded-full">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <path d="M18 15l-6-6-6 6"/>
                    </svg>
                    8.2%
                </span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sales</p>
                <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">1,240</h4>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-warning-50 text-warning-500 dark:bg-warning-500/10">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="flex items-center gap-1 text-xs font-medium text-error-500 bg-error-50 dark:bg-error-500/10 px-2 py-1 rounded-full">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                    3.1%
                </span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Customers</p>
                <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">854</h4>
            </div>
        </div>

        <!-- Stat Card 4 -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-info-50 text-info-500 dark:bg-info-500/10">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="flex items-center gap-1 text-xs font-medium text-success-500 bg-success-50 dark:bg-success-500/10 px-2 py-1 rounded-full">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <path d="M18 15l-6-6-6 6"/>
                    </svg>
                    24.3%
                </span>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Conversion Rate</p>
                <h4 class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">4.82%</h4>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Main Chart -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 lg:col-span-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">Revenue Overview</h3>
                    <p class="text-sm text-gray-500">Track your earnings over time</p>
                </div>
                <select class="rounded-lg border border-gray-200 bg-transparent px-3 py-1.5 text-xs font-medium text-gray-500 focus:outline-none dark:border-gray-800">
                    <option>Last 7 Days</option>
                    <option>Last 30 Days</option>
                    <option>Last 12 Months</option>
                </select>
            </div>
            <div class="h-80 w-full flex items-center justify-center border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-xl">
                <p class="text-gray-400 italic">Chart Placeholder (ApexCharts/Chart.js)</p>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 lg:col-span-4">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white/90">Recent Transactions</h3>
                <a href="#" class="text-xs font-semibold text-brand-500 hover:text-brand-600">View All</a>
            </div>
            <div class="space-y-6">
                @foreach([
                    ['name' => 'John Doe', 'date' => 'Oct 24, 2023', 'amount' => '+$250.00', 'status' => 'Success', 'color' => 'success'],
                    ['name' => 'Sarah Smith', 'date' => 'Oct 23, 2023', 'amount' => '-$45.00', 'status' => 'Pending', 'color' => 'warning'],
                    ['name' => 'Emma Watson', 'date' => 'Oct 22, 2023', 'amount' => '+$1,200.00', 'status' => 'Success', 'color' => 'success'],
                    ['name' => 'Mike Ross', 'date' => 'Oct 21, 2023', 'amount' => '+$85.00', 'status' => 'Failed', 'color' => 'error'],
                ] as $tx)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center font-bold text-gray-500">
                            {{ substr($tx['name'], 0, 1) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 dark:text-white/90">{{ $tx['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $tx['date'] }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold @if(strpos($tx['amount'], '+') !== false) text-success-500 @else text-gray-800 dark:text-white/90 @endif">
                            {{ $tx['amount'] }}
                        </p>
                        <p class="text-[10px] font-medium text-{{ $tx['color'] }}-500">{{ $tx['status'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
