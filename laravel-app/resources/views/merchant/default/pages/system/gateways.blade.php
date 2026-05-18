@extends('merchant.default.layouts.app')

@section('title', 'Payment Gateways')

@section('content')
    <div id="settings-container">
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `Payment Gateways` }">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">Payment Gateways</h2>
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
                            <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">Payment Gateways</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Breadcrumb End -->

            <!-- Category Navigation Tabs Start -->
            <div class="mb-6 flex flex-wrap gap-2 rounded-xl bg-gray-100 p-1 dark:bg-gray-800/40 w-fit">
                <button type="button" onclick="switchCategory('all', this)"
                    class="tab-btn active bg-brand-500 text-white font-semibold shadow-theme-xs rounded-lg px-4 py-2 text-sm transition">
                    All Gateways
                </button>
                <button type="button" onclick="switchCategory('mfs', this)"
                    class="tab-btn text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-medium rounded-lg px-4 py-2 text-sm transition">
                    MFS Gateways
                </button>
                <button type="button" onclick="switchCategory('bank', this)"
                    class="tab-btn text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-medium rounded-lg px-4 py-2 text-sm transition">
                    Bank Transfer
                </button>
                <button type="button" onclick="switchCategory('global', this)"
                    class="tab-btn text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white font-medium rounded-lg px-4 py-2 text-sm transition">
                    Global Gateways
                </button>
            </div>
            <!-- Category Navigation Tabs End -->

            <!-- Main Gateways Card Start -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                <!-- Card Header Start -->
                <div class="px-4 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Merchant Checkout Gateways</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Activate, configure, and override payment settlement structures tailored for checkout themes.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'create-gateway-modal' } }))"
                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add New Gateway
                        </button>
                    </div>
                </div>
                <!-- Card Header End -->

                <!-- Data Table Filter Start -->
                <div class="mb-4 flex flex-col gap-3 px-4 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Show</span>
                            <div class="relative bg-transparent">
                                <select id="show-limit"
                                    class="dark:bg-dark-900 h-9 appearance-none rounded-lg border border-gray-300 bg-transparent py-1.5 pl-3 pr-8 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                            <div class="relative bg-transparent">
                                <select id="filter-status"
                                    class="dark:bg-dark-900 h-9 appearance-none rounded-lg border border-gray-300 bg-transparent py-1.5 pl-3 pr-8 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <!-- Bulk Actions Container -->
                        <div id="bulk-actions-container" class="hidden flex items-center gap-2 pl-2 border-l border-gray-200 dark:border-gray-800">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400" id="select-count-label">0 selected</span>
                            <div class="relative bg-transparent">
                                <select id="bulk-action-select"
                                    class="dark:bg-dark-900 h-9 appearance-none rounded-lg border border-gray-300 bg-transparent py-1.5 pl-3 pr-8 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">Bulk Action</option>
                                    <option value="activated">Activate Selected</option>
                                    <option value="inactivated">Deactivate Selected</option>
                                    <option value="deleted">Delete Selected</option>
                                </select>
                                <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                                    <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </span>
                            </div>
                            <button type="button" id="apply-bulk-action-btn"
                                class="bg-gray-800 hover:bg-gray-900 text-white shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg px-3 py-1.5 text-sm font-medium transition dark:bg-brand-500 dark:hover:bg-brand-600">
                                Apply
                            </button>
                        </div>
                    </div>

                    <div class="relative">
                        <button type="button" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z"></path>
                            </svg>
                        </button>
                        <input type="text" id="search-input" placeholder="Search Gateways..."
                            oninput="debouncedLoadGateways()"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 xl:w-[300px]">
                    </div>
                </div>
                <!-- Data Table Filter End -->

                <!-- Table Area Start -->
                <div class="custom-scrollbar max-w-full overflow-x-auto overflow-y-visible px-5 sm:px-6">
                    <table class="min-w-full">
                        <thead class="border-y border-gray-100 py-3 dark:border-gray-800">
                            <tr>
                                <th class="px-4 py-3 font-normal whitespace-nowrap w-10">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="select-all-checkbox"
                                            class="h-4 w-4 rounded-sm border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 transition">
                                    </div>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-left">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Gateway</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-left">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Display Name</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-center">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Settlement Currency</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-center">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Status</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-right">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Actions</p>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="gateway-table-body" class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr>
                                <td colspan="6" class="px-5 py-20 text-center">
                                    <x-loader show-text="true" text="Loading gateways..." />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Table Area End -->

                <!-- Table Footer Start -->
                <div class="px-4 py-4 sm:px-6 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div id="table-info" class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Showing 0 to 0 of 0 entries
                        </div>
                        <div id="table-pagination">
                            <!-- Pagination will be loaded via AJAX -->
                        </div>
                    </div>
                </div>
                <!-- Table Footer End -->
            </div>
            <!-- Main Gateways Card End -->
        </div>

        <!-- Create Gateway Modal -->
        <x-m::modal id="create-gateway-modal" type="brand" title="Add Payment Gateway"
            description="Choose a dynamic MFS/Global payment engine or initiate a manual Bank setup." actionTitle="Create Setup"
            actionId="submit-create-gateway-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </x-slot>

            <form id="create-gateway-form" class="space-y-4" x-data="{ category: 'bank', gatewayVal: 'bank' }">
                @csrf
                <!-- Hidden input that holds the actual gateway value to submit -->
                <input type="hidden" name="gateway" :value="gatewayVal" id="hidden-gateway-input">

                <div>
                    <label class="mb-3 block text-sm font-semibold text-gray-800 dark:text-gray-200">Select Gateway Category</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Card 1: Bank Gateway -->
                        <div @click="category = 'bank'; gatewayVal = 'bank'"
                            class="relative cursor-pointer border-2 rounded-xl p-5 flex flex-col items-center text-center transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5"
                            :class="category === 'bank' ? 'border-brand-500 bg-brand-500/[0.02] dark:border-brand-500 dark:bg-brand-500/[0.04]' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] hover:border-gray-300 dark:hover:border-gray-700'">
                            
                            <!-- Selected Check Indicator -->
                            <div x-show="category === 'bank'" class="absolute top-3 right-3 text-brand-500 bg-brand-50 dark:bg-brand-500/10 p-1 rounded-full">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>

                            <div class="h-12 w-12 rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500 flex items-center justify-center mb-4 transition-colors"
                                :class="category === 'bank' ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-500' : 'bg-gray-50 dark:bg-gray-800 text-gray-400'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Manual Bank Gateway</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 leading-relaxed max-w-[200px]">Direct manual bank transfer setup with customized deposit guidelines.</p>
                        </div>

                        <!-- Card 2: Payment Gateway -->
                        <div @click="category = 'payment'; gatewayVal = ''"
                            class="relative cursor-pointer border-2 rounded-xl p-5 flex flex-col items-center text-center transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5"
                            :class="category === 'payment' ? 'border-brand-500 bg-brand-500/[0.02] dark:border-brand-500 dark:bg-brand-500/[0.04]' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-white/[0.03] hover:border-gray-300 dark:hover:border-gray-700'">
                            
                            <!-- Selected Check Indicator -->
                            <div x-show="category === 'payment'" class="absolute top-3 right-3 text-brand-500 bg-brand-50 dark:bg-brand-500/10 p-1 rounded-full">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>

                            <div class="h-12 w-12 rounded-lg bg-gray-50 dark:bg-gray-800 text-brand-500 flex items-center justify-center mb-4 transition-colors"
                                :class="category === 'payment' ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-500' : 'bg-gray-50 dark:bg-gray-800 text-gray-400'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white">Payment Gateway</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 leading-relaxed max-w-[200px]">Automated online payment gateway solutions like bKash, Nagad, etc.</p>
                        </div>
                    </div>
                </div>

                <!-- Custom dropdown search shown ONLY when dynamic Payment Gateway is active -->
                <div x-show="category === 'payment'" x-transition class="space-y-2 pt-3" x-data="{ open: false, search: '', selectedLabel: 'Select payment gateway...' }">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">Select Automated Engine <span class="text-danger">*</span></label>
                    
                    <div class="relative">
                        <button type="button" @click="open = !open" 
                            class="h-11 w-full flex items-center justify-between rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-800 dark:text-white/90 shadow-theme-xs focus:border-brand-500 focus:outline-hidden text-left transition-colors">
                            <span x-text="selectedLabel" class="truncate font-medium"></span>
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400 stroke-current transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>

                        <!-- Dropdown panel -->
                        <div x-show="open" @click.outside="open = false" x-transition
                            class="absolute left-0 right-0 z-9999 mt-1.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#1E2635] p-2 shadow-lg flex flex-col"
                            style="max-height: 280px;">
                            
                            <!-- Search box inside dropdown (Stay Fixed, Never Scroll) -->
                            <div class="relative mb-2 shrink-0">
                                <input type="text" x-model="search" placeholder="Search payment engine..."
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 pl-9 text-xs text-gray-800 dark:text-white focus:border-brand-500 focus:outline-hidden"
                                    style="height: 38px; line-height: 38px;">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </span>
                            </div>

                            <!-- Scrollable Options List Container with complete wheel scroll isolation -->
                            <div class="space-y-0.5 pr-1"
                                 @wheel.stop=""
                                 style="max-height: 180px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                                @foreach($availableGateways as $slug => $info)
                                    <button type="button" 
                                        x-show="'{{ strtolower($info['title']) }}'.includes(search.toLowerCase())"
                                        @click="gatewayVal = '{{ $slug }}'; selectedLabel = '{{ $info['title'] }} ({{ ucfirst($info['tab'] ?? '') }})'; open = false; search = ''"
                                        class="w-full text-left px-3 py-2 text-xs rounded-md hover:bg-brand-50 dark:hover:bg-brand-500/10 hover:text-brand-600 dark:hover:text-brand-400 text-gray-700 dark:text-gray-300 font-medium transition-colors"
                                        :class="gatewayVal === '{{ $slug }}' ? 'bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 font-semibold' : ''">
                                        {{ $info['title'] }} ({{ ucfirst($info['tab'] ?? '') }})
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </x-m::modal>

        <!-- Delete Gateway Modal -->
        <x-m::modal id="delete-gateway-modal" type="brand" title="Delete Payment Gateway"
            description="Are you sure you want to delete this payment gateway? All credentials and parameter mappings will be cleared permanently. This action cannot be undone." actionTitle="Delete Gateway"
            actionId="confirm-delete-gateway-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
            </x-slot>
        </x-m::modal>

        <!-- Bulk Action Confirmation Modal -->
        <x-m::modal id="bulk-action-gateway-modal" type="brand" title="Confirm Bulk Action"
            description="Are you sure you want to apply this bulk action to the selected payment gateways?"
            actionTitle="Apply Action" actionId="confirm-bulk-action-gateway-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </x-slot>
        </x-m::modal>
    </div>

    <script>
        (function() {
            let currentCategory = 'all';
            let selectedIds = [];
            let gatewayIdToDelete = null;
            let pendingBulkAction = '';

            // Category Tab Switching
            window.switchCategory = function(category, element) {
                currentCategory = category;
                
                // Toggle active state
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active', 'bg-brand-500', 'text-white', 'font-semibold', 'shadow-theme-xs');
                    btn.classList.add('text-gray-600', 'dark:text-gray-400', 'font-medium');
                });
                
                element.classList.remove('text-gray-600', 'dark:text-gray-400', 'font-medium');
                element.classList.add('active', 'bg-brand-500', 'text-white', 'font-semibold', 'shadow-theme-xs');
                
                loadGateways(1);
            }

            // AJAX load gateways using fetch
            window.loadGateways = function(page = 1) {
                const search = document.getElementById('search-input') ? document.getElementById('search-input').value : '';
                const status = document.getElementById('filter-status') ? document.getElementById('filter-status').value : '';
                const showLimit = document.getElementById('show-limit') ? document.getElementById('show-limit').value : '10';
                const tableBody = document.getElementById('gateway-table-body');

                if (!tableBody) return;

                // Show loader while loading
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-5 py-20 text-center">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <div class="w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Loading gateways...</span>
                            </div>
                        </td>
                    </tr>
                `;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('page', page);
                formData.append('show_limit', showLimit);
                formData.append('filter_status', status);
                formData.append('search_input', search);
                formData.append('tabType', currentCategory);

                fetch('{{ route("merchant.system.gateways.list") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(response => {
                    // Reset checkboxes
                    selectedIds = [];
                    const selectAll = document.getElementById('select-all-checkbox');
                    if (selectAll) selectAll.checked = false;
                    updateBulkContainer();

                    if (response.status === 'true') {
                        let html = '';
                        response.response.forEach(item => {
                            let statusBadge = item.status === 'active' 
                                ? '<span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-400">Active</span>' 
                                : '<span class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-semibold text-gray-600 dark:bg-white/5 dark:text-gray-400">Inactive</span>';
                            
                            html += `
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all border-b border-gray-100 dark:border-gray-800 last:border-0">
                                    <td class="px-4 py-4 w-10">
                                        <div class="flex items-center">
                                            <input type="checkbox" value="${item.id}" onchange="toggleSelect('${item.id}', this)"
                                                class="row-checkbox h-4 w-4 rounded-sm border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 transition">
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-sm font-semibold text-gray-800 dark:text-white/90">${item.name}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">${item.display}</span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-md bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">${item.currency}</span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        ${statusBadge}
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div x-data="{openDropDown: false}" class="relative inline-block text-left">
                                            <button @click="openDropDown = !openDropDown" class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:bg-white/[0.03] dark:text-gray-400 dark:hover:bg-white/[0.05] transition-colors shadow-theme-xs border border-gray-200 dark:border-gray-800">
                                                Actions
                                                <svg class="duration-200 ease-in-out fill-current" :class="openDropDown && 'rotate-180'" width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                            </button>
                                            <div x-show="openDropDown" @click.outside="openDropDown = false" x-transition
                                                class="absolute right-0 top-full z-40 mt-2 w-[260px] rounded-2xl border border-gray-200 bg-white p-1.5 shadow-theme-lg dark:border-gray-800 dark:bg-[#1E2635]"
                                                style="display: none;">
                                                <ul class="flex flex-col gap-0.5">
                                                    <li>
                                                        <a href="/merchant/system/gateways/${item.id}/edit"
                                                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                                                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                            Configure Gateway
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button @click="openDropDown = false; deleteGateway('${item.id}')"
                                                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                                                            <svg class="h-5 w-5 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                            Delete Gateway
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        tableBody.innerHTML = html;
                        
                        // Re-initialize Alpine tree so that newly generated dropdown elements compile correctly
                        if (window.Alpine) {
                            Alpine.initTree(tableBody);
                        }
                    } else {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="px-5 py-20 text-center text-gray-500 dark:text-gray-400 font-medium">
                                    No active configurations found for this category.
                                </td>
                            </tr>
                        `;
                    }

                    document.getElementById('table-info').innerHTML = response.datatableInfo || 'Showing 0 to 0 of 0 entries';
                    document.getElementById('table-pagination').innerHTML = response.pagination || '';
                })
                .catch(err => {
                    console.error(err);
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-5 py-20 text-center text-red-500 font-medium">
                                Something went wrong while loading gateway details.
                            </td>
                        </tr>
                    `;
                });
            }

            // Debounced Search Inputs
            let debounceTimer;
            window.debouncedLoadGateways = function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    loadGateways(1);
                }, 400);
            }

            // Deletion Handler
            window.deleteGateway = function(id) {
                gatewayIdToDelete = id;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'delete-gateway-modal' } }));
            }

            // Selection & Bulk Handlers
            window.toggleSelect = function(id, checkbox) {
                if (checkbox.checked) {
                    if (!selectedIds.includes(id)) selectedIds.push(id);
                } else {
                    selectedIds = selectedIds.filter(x => x !== id);
                }
                updateBulkContainer();
            }

            window.updateBulkContainer = function() {
                const bulkContainer = document.getElementById('bulk-actions-container');
                const selectCountLabel = document.getElementById('select-count-label');
                if (selectedIds.length > 0) {
                    if (bulkContainer) bulkContainer.classList.remove('hidden');
                    if (selectCountLabel) selectCountLabel.textContent = `${selectedIds.length} selected`;
                } else {
                    if (bulkContainer) bulkContainer.classList.add('hidden');
                }
            }

            // Bulk actions processor
            window.applyBulkAction = function() {
                const actionSelect = document.getElementById('bulk-action-select');
                const action = actionSelect ? actionSelect.value : '';
                if (!action) {
                    showToast('error', 'Please select a valid bulk action.');
                    return;
                }

                pendingBulkAction = action;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'bulk-action-gateway-modal' } }));
            }

            // Create Gateway Modal Submit
            window.submitCreateGateway = function() {
                const inputElement = document.getElementById('hidden-gateway-input');
                const gateway = inputElement ? inputElement.value : '';
                if (!gateway) {
                    showToast('error', 'Please select a valid gateway engine type.');
                    return;
                }

                const btn = document.getElementById('submit-create-gateway-btn');
                const originalText = btn ? btn.innerHTML : 'Create Setup';
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Saving...';
                }

                const formData = new FormData(document.getElementById('create-gateway-form'));

                fetch('{{ route("merchant.system.gateways.create") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(response => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'create-gateway-modal' } }));

                    if (response.status === 'true') {
                        showToast('success', response.message || 'Channel engine initiated successfully.');
                        loadGateways(1);
                    } else {
                        showToast('error', response.message || 'Could not instantiate gateway.');
                    }
                })
                .catch(() => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                    showToast('error', 'An error occurred while creating this gateway channel.');
                });
            }

            // Hook listeners dynamically
            function initializeListeners() {
                // Select-all checkbox
                const selectAllBox = document.getElementById('select-all-checkbox');
                if (selectAllBox) {
                    selectAllBox.onchange = function() {
                        const isChecked = this.checked;
                        document.querySelectorAll('.row-checkbox').forEach(cb => {
                            cb.checked = isChecked;
                            toggleSelect(cb.value, cb);
                        });
                    };
                }

                // Limit change
                const limitSelect = document.getElementById('show-limit');
                if (limitSelect) {
                    limitSelect.onchange = () => loadGateways(1);
                }

                // Status filter change
                const statusSelect = document.getElementById('filter-status');
                if (statusSelect) {
                    statusSelect.onchange = () => loadGateways(1);
                }

                // Apply bulk action click
                const bulkBtn = document.getElementById('apply-bulk-action-btn');
                if (bulkBtn) {
                    bulkBtn.onclick = applyBulkAction;
                }

                // Submit create gateway click
                const submitCreateBtn = document.getElementById('submit-create-gateway-btn');
                if (submitCreateBtn) {
                    submitCreateBtn.onclick = submitCreateGateway;
                }

                // Confirm delete gateway click
                const confirmDeleteBtn = document.getElementById('confirm-delete-gateway-btn');
                if (confirmDeleteBtn) {
                    confirmDeleteBtn.onclick = function() {
                        if (!gatewayIdToDelete) return;
                        
                        const originalText = confirmDeleteBtn.innerHTML;
                        confirmDeleteBtn.disabled = true;
                        confirmDeleteBtn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Deleting...';

                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');

                        fetch(`/merchant/system/gateways/${gatewayIdToDelete}/delete`, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(response => {
                            confirmDeleteBtn.disabled = false;
                            confirmDeleteBtn.innerHTML = originalText;
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'delete-gateway-modal' } }));
                            if (response.status === 'true') {
                                showToast('success', response.message || 'Payment channel has been successfully removed.');
                                loadGateways(1);
                            } else {
                                showToast('error', response.message || 'Something went wrong while removing the channel.');
                            }
                        })
                        .catch(() => {
                            confirmDeleteBtn.disabled = false;
                            confirmDeleteBtn.innerHTML = originalText;
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'delete-gateway-modal' } }));
                            showToast('error', 'Network error. Please try again.');
                        });
                    };
                }

                // Confirm bulk action click
                const confirmBulkBtn = document.getElementById('confirm-bulk-action-gateway-btn');
                if (confirmBulkBtn) {
                    confirmBulkBtn.onclick = function() {
                        if (!pendingBulkAction || selectedIds.length === 0) return;

                        const originalText = confirmBulkBtn.innerHTML;
                        confirmBulkBtn.disabled = true;
                        confirmBulkBtn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Updating...';

                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');
                        formData.append('actionID', pendingBulkAction);
                        formData.append('selected_ids', JSON.stringify(selectedIds));

                        fetch('{{ route("merchant.system.gateways.bulk") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(response => {
                            confirmBulkBtn.disabled = false;
                            confirmBulkBtn.innerHTML = originalText;
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'bulk-action-gateway-modal' } }));
                            if (response.status === 'true') {
                                showToast('success', response.message || 'Batch update completed successfully.');
                                selectedIds = [];
                                updateBulkContainer();
                                const selectAllCheckbox = document.getElementById('select-all-checkbox');
                                if (selectAllCheckbox) selectAllCheckbox.checked = false;
                                loadGateways(1);
                            } else {
                                showToast('error', response.message || 'Something went wrong.');
                            }
                        })
                        .catch(() => {
                            confirmBulkBtn.disabled = false;
                            confirmBulkBtn.innerHTML = originalText;
                            window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'bulk-action-gateway-modal' } }));
                            showToast('error', 'Network error. Please try again.');
                        });
                    };
                }

                // Intercept pagination clicks dynamically
                const paginationEl = document.getElementById('table-pagination');
                if (paginationEl) {
                    paginationEl.onclick = function(e) {
                        const link = e.target.closest('.page-link');
                        if (link) {
                            e.preventDefault();
                            const page = link.getAttribute('data-page');
                            if (page) {
                                loadGateways(page);
                            }
                        }
                    };
                }

                // Initial Load
                loadGateways(1);
            }

            // Immediate execution (supports direct/PJAX navigations)
            initializeListeners();
        })();
    </script>
@endsection
