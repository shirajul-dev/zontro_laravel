@extends('merchant.default.layouts.app')

@section('title', 'API Credentials')

@section('content')
    <div id="settings-container">
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `API Credentials` }">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">API Credentials</h2>
                    <nav>
                        <ol class="flex items-center gap-1.5">
                            <li>
                                <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                                    href="{{ route('merchant.settings') }}">
                                    Brand Settings
                                    <svg class="stroke-current" width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </a>
                            </li>
                            <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">API Credentials</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Breadcrumb End -->

            <!-- Endpoints Card Start -->
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                <div class="px-4 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Integration API Endpoints</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Use these dynamic root endpoints to hook up your checkouts.</p>
                </div>
                <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">Base Gateway URL</label>
                        <div class="flex rounded-lg shadow-theme-xs">
                            <input type="text" readonly value="{{ url('/api') }}" id="endpoint-base"
                                class="h-10 w-full rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            <button onclick="copyToClipboard('{{ url('/api') }}', 'endpoint-base-btn')" id="endpoint-base-btn"
                                class="inline-flex h-10 items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 px-4 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                Copy
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">Gateway Checkout Redirection</label>
                        <div class="flex rounded-lg shadow-theme-xs">
                            <input type="text" readonly value="{{ url('/api/checkout/redirect') }}" id="endpoint-redirect"
                                class="h-10 w-full rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            <button onclick="copyToClipboard('{{ url('/api/checkout/redirect') }}', 'endpoint-redirect-btn')" id="endpoint-redirect-btn"
                                class="inline-flex h-10 items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 px-4 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                Copy
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">Verify Payment Endpoint</label>
                        <div class="flex rounded-lg shadow-theme-xs">
                            <input type="text" readonly value="{{ url('/api/verify-payment') }}" id="endpoint-verify"
                                class="h-10 w-full rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            <button onclick="copyToClipboard('{{ url('/api/verify-payment') }}', 'endpoint-verify-btn')" id="endpoint-verify-btn"
                                class="inline-flex h-10 items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 px-4 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                Copy
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-400">Refund Payment Endpoint</label>
                        <div class="flex rounded-lg shadow-theme-xs">
                            <input type="text" readonly value="{{ url('/api/refund-payment') }}" id="endpoint-refund"
                                class="h-10 w-full rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            <button onclick="copyToClipboard('{{ url('/api/refund-payment') }}', 'endpoint-refund-btn')" id="endpoint-refund-btn"
                                class="inline-flex h-10 items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 px-4 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Endpoints Card End -->

            <!-- Main Credentials Card Start -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                <!-- Card Header Start -->
                <div class="px-4 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">API Credentials</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Create and manage merchant access credentials and scopes.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'create-api-modal' } }))"
                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create API Key
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
                                    <option value="expired">Expired</option>
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
                        <input type="text" id="search-input" placeholder="Search API Keys..."
                            oninput="debouncedLoadApiKeys()"
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
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Name</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-left">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">API Credentials / Secret</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-left">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Created / Expiry Date</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-center">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Status</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-right">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Actions</p>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="api-table-body" class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr>
                                <td colspan="6" class="px-5 py-20 text-center">
                                    <x-loader show-text="true" text="Loading API keys..." />
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
            <!-- Main Credentials Card End -->
        </div>

        <!-- Modals Layout Area -->

        <!-- Create API Key Modal -->
        <x-m::modal id="create-api-modal" type="brand" title="Create API Key"
            description="Generate a new merchant credential token with specific access privileges." actionTitle="Generate"
            actionId="submit-create-api-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-9-3h.01M5.071 19.071c1.758-1.758 4.29-2.012 6.315-.815l7.15-7.15a3 3 0 114.243 4.243l-7.15 7.15c-1.197 2.025-1.451 4.557.307 6.315a6.002 6.002 0 01-8.544 0 6.002 6.002 0 010-8.544z"></path>
                    </svg>
                </div>
            </x-slot>

            <form id="create-api-form" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">API Key Name <span class="text-danger">*</span></label>
                    <input type="text" name="api_name" placeholder="WooCommerce Store Live" required
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Expire Date</label>
                    <input type="date" name="apiExpiryDate"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <p class="mt-1 text-xs text-gray-400">Leave blank for no expiration date.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Access Scopes / Permissions</label>
                    <div class="rounded-xl border border-gray-150 p-4 dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.01] grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2.5 text-sm text-gray-800 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="api_scopes[]" value="create_payment" checked
                                class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <span>Create Payment</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-gray-800 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="api_scopes[]" value="verify_payment" checked
                                class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <span>Verify Payment</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-gray-800 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="api_scopes[]" value="refund_payment" checked
                                class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <span>Refund Payment</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-gray-800 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="api_scopes[]" value="view_balance" checked
                                class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <span>View Balance</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-gray-800 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="api_scopes[]" value="view_transactions" checked
                                class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <span>View Transactions</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status <span class="text-danger">*</span></label>
                    <div class="relative bg-transparent">
                        <select name="api_status" required
                            class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                    </div>
                </div>
            </form>
        </x-m::modal>

        <!-- Edit API Key Modal -->
        <x-m::modal id="edit-api-modal" type="brand" title="Edit API Key"
            description="Modify names, expiry constraints, or scope configurations." actionTitle="Save Changes"
            actionId="submit-edit-api-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
            </x-slot>

            <form id="edit-api-form" class="space-y-4">
                @csrf
                <input type="hidden" name="api_id" id="edit_api_id">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">API Key Name <span class="text-danger">*</span></label>
                    <input type="text" name="api_name" id="edit_api_name" required
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Expire Date</label>
                    <input type="date" name="apiExpiryDate" id="edit_api_expiry"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <p class="mt-1 text-xs text-gray-400">Leave blank for no expiration date.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Access Scopes / Permissions</label>
                    <div class="rounded-xl border border-gray-150 p-4 dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.01] grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2.5 text-sm text-gray-800 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="api_scopes[]" value="create_payment" id="scope_create_payment"
                                class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <span>Create Payment</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-gray-800 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="api_scopes[]" value="verify_payment" id="scope_verify_payment"
                                class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <span>Verify Payment</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-gray-800 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="api_scopes[]" value="refund_payment" id="scope_refund_payment"
                                class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <span>Refund Payment</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-gray-800 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="api_scopes[]" value="view_balance" id="scope_view_balance"
                                class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <span>View Balance</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-gray-800 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" name="api_scopes[]" value="view_transactions" id="scope_view_transactions"
                                class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                            <span>View Transactions</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status <span class="text-danger">*</span></label>
                    <div class="relative bg-transparent">
                        <select name="api_status" id="edit_api_status" required
                            class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                    </div>
                </div>
            </form>
        </x-m::modal>

        <!-- Delete API Key Modal -->
        <x-m::modal id="delete-api-modal" type="brand" title="Delete API Key"
            description="Are you sure you want to delete this API Key? This action is permanent and cannot be undone." actionTitle="Delete API Key"
            actionId="confirm-delete-api-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
            </x-slot>
        </x-m::modal>

        <!-- Bulk Action Confirmation Modal -->
        <x-m::modal id="bulk-action-modal" type="brand" title="Confirm Bulk Action"
            description="Are you sure you want to apply this bulk action to all selected API credentials?"
            actionTitle="Apply Action" actionId="confirm-bulk-action-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            </x-slot>
        </x-m::modal>

        <!-- Script Section Start -->
        <script>
            // Clipboard Copy Widget Helper
            function copyToClipboard(text, buttonId) {
                navigator.clipboard.writeText(text).then(function() {
                    const btn = document.getElementById(buttonId);
                    const originalText = btn.innerHTML;
                    
                    // Style button green to show success
                    btn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                    btn.classList.add('bg-green-500', 'text-white');
                    btn.innerHTML = 'Copied!';

                    showToast('success', 'Copied to clipboard successfully.');

                    setTimeout(() => {
                        btn.classList.remove('bg-green-500', 'text-white');
                        btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                        btn.innerHTML = originalText;
                    }, 2000);
                }, function(err) {
                    showToast('error', 'Failed to copy to clipboard.');
                });
            }

            (function() {
                let currentPage = 1;
                let activeDeleteId = null;
                let activeBulkAction = null;
                let searchTimeout = null;

                // Load API Keys list
                window.loadApiKeys = async function(page = 1) {
                    currentPage = page;
                    const limit = document.getElementById('show-limit')?.value || 10;
                    const search = document.getElementById('search-input')?.value || '';
                    const status = document.getElementById('filter-status')?.value || '';

                    const tbody = document.getElementById('api-table-body');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-5 py-20 text-center">
                                <x-loader show-text="true" text="Loading API keys..." />
                            </td>
                        </tr>
                    `;

                    try {
                        const response = await fetch("{{ route('merchant.settings.api-keys.list') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                page: page,
                                show_limit: limit,
                                search_input: search,
                                filter_status: status
                            })
                        });

                        const result = await response.json();
                        if (result.status === 'true' && result.response && result.response.length > 0) {
                            let rowsHtml = '';
                            result.response.forEach(item => {
                                let statusBadge = '';
                                if (item.status === 'active') {
                                    statusBadge = '<span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-400">Active</span>';
                                } else if (item.status === 'inactive') {
                                    statusBadge = '<span class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-semibold text-gray-600 dark:bg-white/5 dark:text-gray-400">Inactive</span>';
                                } else {
                                    statusBadge = '<span class="inline-flex items-center rounded-full bg-error-50 px-2.5 py-0.5 text-xs font-semibold text-error-700 dark:bg-error-500/10 dark:text-error-400">Expired</span>';
                                }

                                // Truncate token visual
                                const displayKey = item.api_key.substring(0, 12) + '...' + item.api_key.substring(item.api_key.length - 8);
                                const randomId = 'btn-copy-' + item.id;

                                rowsHtml += `
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all border-b border-gray-100 dark:border-gray-800 last:border-0">
                                        <td class="px-4 py-4 w-10">
                                            <div class="flex items-center">
                                                <input type="checkbox" value="${item.id}"
                                                    class="row-checkbox h-4 w-4 rounded-sm border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 transition">
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 font-semibold text-gray-800 dark:text-white/90">
                                            ${item.name}
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-2 max-w-[280px]">
                                                <span class="font-mono text-sm text-gray-600 dark:text-gray-400 select-all">${displayKey}</span>
                                                <button onclick="copyToClipboard('${item.api_key}', '${randomId}')" id="${randomId}"
                                                    class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m-5 4h5m-5 4h5m-5 4h5"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                                            <div class="font-medium">${item.created_date}</div>
                                            <div class="text-xs text-gray-400">Expires: ${item.expired_date}</div>
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
                                                            <button @click="openDropDown = false; editApiKey('${item.id}')"
                                                                class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 transition-colors text-left">
                                                                <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                </svg>
                                                                Edit Key
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button @click="openDropDown = false; deleteApiKeyPrompt('${item.id}')"
                                                                class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 transition-colors text-left">
                                                                <svg class="h-5 w-5 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                </svg>
                                                                Delete Key
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            });
                            tbody.innerHTML = rowsHtml;
                            document.getElementById('table-info').innerHTML = result.datatableInfo;
                            document.getElementById('table-pagination').innerHTML = result.pagination;

                            // Re-bind pagination buttons
                            document.querySelectorAll('#table-pagination button[data-page]').forEach(btn => {
                                btn.onclick = function() {
                                    loadApiKeys(parseInt(this.dataset.page));
                                };
                            });

                            // Bind checkboxes & bulk actions
                            bindCheckboxes();

                            // Re-initialize Alpine for dropdowns
                            if (window.Alpine) {
                                Alpine.initTree(tbody);
                            }
                        } else {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="6" class="px-5 py-20 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0L12 17l-8-4"></path>
                                            </svg>
                                            <span class="font-medium text-base">No API keys found</span>
                                            <span class="text-sm text-gray-400">Create a new key to begin integrating payments.</span>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            document.getElementById('table-info').innerHTML = 'Showing 0 to 0 of 0 entries';
                            document.getElementById('table-pagination').innerHTML = '';
                        }
                    } catch (error) {
                        tbody.innerHTML = `<tr><td colspan="6" class="px-5 py-20 text-center text-red-600 font-medium">Failed to load API keys. Please try again.</td></tr>`;
                    }
                }

                // Debounce search inputs
                window.debouncedLoadApiKeys = debounce(() => loadApiKeys(1), 500);

                function debounce(func, wait) {
                    let timeout;
                    return function(...args) {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => func.apply(this, args), wait);
                    };
                }

                // Row selection & bulk actions logic
                function bindCheckboxes() {
                    const selectAll = document.getElementById('select-all-checkbox');
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    const bulkContainer = document.getElementById('bulk-actions-container');
                    const countLabel = document.getElementById('select-count-label');

                    if (selectAll) {
                        selectAll.checked = false;
                        selectAll.onchange = function() {
                            checkboxes.forEach(chk => chk.checked = selectAll.checked);
                            updateBulkContainer();
                        };
                    }

                    checkboxes.forEach(chk => {
                        chk.onchange = function() {
                            if (selectAll) {
                                selectAll.checked = [...checkboxes].every(c => c.checked);
                            }
                            updateBulkContainer();
                        };
                    });

                    function updateBulkContainer() {
                        const selected = document.querySelectorAll('.row-checkbox:checked');
                        if (selected.length > 0) {
                            bulkContainer.classList.remove('hidden');
                            bulkContainer.classList.add('flex');
                            countLabel.innerText = `${selected.length} selected`;
                        } else {
                            bulkContainer.classList.add('hidden');
                            bulkContainer.classList.remove('flex');
                        }
                    }
                }

                // Trigger Bulk Action Confirmation Modal
                const applyBulkBtn = document.getElementById('apply-bulk-action-btn');
                if (applyBulkBtn) {
                    applyBulkBtn.onclick = function() {
                        const action = document.getElementById('bulk-action-select').value;
                        if (!action) {
                            showToast('error', 'Please choose a bulk action to proceed.');
                            return;
                        }
                        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
                        if (selectedIds.length === 0) {
                            showToast('error', 'Please select at least one API key.');
                            return;
                        }
                        activeBulkAction = action;
                        window.dispatchEvent(new CustomEvent('open-modal', {
                            detail: { id: 'bulk-action-modal' }
                        }));
                    };
                }

                const confirmBulkBtn = document.getElementById('confirm-bulk-action-btn');
                if (confirmBulkBtn) {
                    confirmBulkBtn.onclick = async function() {
                        if (!activeBulkAction) return;

                        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
                        this.disabled = true;
                        const originalText = this.innerHTML;
                        this.innerHTML = `<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>`;

                        try {
                            const response = await fetch("{{ route('merchant.settings.api-keys.bulk') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    actionID: activeBulkAction,
                                    selected_ids: JSON.stringify(selectedIds)
                                })
                            });

                            const result = await response.json();
                            if (result.status === 'true') {
                                showToast('success', result.message);
                                window.dispatchEvent(new CustomEvent('close-modal', {
                                    detail: { id: 'bulk-action-modal' }
                                }));
                                document.getElementById('bulk-action-select').value = '';
                                loadApiKeys(currentPage);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'Bulk action failed.');
                        } finally {
                            this.disabled = false;
                            this.innerHTML = originalText;
                            activeBulkAction = null;
                        }
                    };
                }

                // Create API Key submit
                const cBtn = document.getElementById('submit-create-api-btn');
                if (cBtn) {
                    cBtn.onclick = function() {
                        document.getElementById('create-api-form').requestSubmit();
                    };
                }

                const cForm = document.getElementById('create-api-form');
                if (cForm) {
                    cForm.onsubmit = async function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        try {
                            const response = await fetch("{{ route('merchant.settings.api-keys.create') }}", {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                            const result = await response.json();
                            if (result.status === 'true') {
                                showToast('success', result.message);
                                window.dispatchEvent(new CustomEvent('close-modal', {
                                    detail: { id: 'create-api-modal' }
                                }));
                                cForm.reset();
                                loadApiKeys(1);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'API Key creation failed.');
                        }
                    };
                }

                // Edit API Key logic
                window.editApiKey = async function(id) {
                    try {
                        const response = await fetch(`/merchant/settings/api-keys/${id}/info`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();

                        if (data.status === 'true') {
                            document.getElementById('edit_api_id').value = id;
                            document.getElementById('edit_api_name').value = data.name;
                            document.getElementById('edit_api_expiry').value = data.expired_date === '--' ? '' : data.expired_date;
                            document.getElementById('edit_api_status').value = data.astatus;

                            // Reset checkboxes
                            document.querySelectorAll('#edit-api-form input[type="checkbox"]').forEach(cb => cb.checked = false);

                            // Apply scopes
                            if (data.api_scopes && Array.isArray(data.api_scopes)) {
                                data.api_scopes.forEach(scope => {
                                    const cb = document.getElementById('scope_' + scope);
                                    if (cb) cb.checked = true;
                                });
                            }

                            window.dispatchEvent(new CustomEvent('open-modal', {
                                detail: { id: 'edit-api-modal' }
                            }));
                        } else {
                            showToast('error', 'Unable to fetch details.');
                        }
                    } catch (error) {
                        showToast('error', 'Error occurred.');
                    }
                }

                const eBtn = document.getElementById('submit-edit-api-btn');
                if (eBtn) {
                    eBtn.onclick = function() {
                        document.getElementById('edit-api-form').requestSubmit();
                    };
                }

                const eForm = document.getElementById('edit-api-form');
                if (eForm) {
                    eForm.onsubmit = async function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        try {
                            const response = await fetch("{{ route('merchant.settings.api-keys.edit') }}", {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                            const result = await response.json();
                            if (result.status === 'true') {
                                showToast('success', result.message);
                                window.dispatchEvent(new CustomEvent('close-modal', {
                                    detail: { id: 'edit-api-modal' }
                                }));
                                loadApiKeys(currentPage);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'Update failed.');
                        }
                    };
                }

                // Delete API Key logic
                window.deleteApiKeyPrompt = function(id) {
                    activeDeleteId = id;
                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: { id: 'delete-api-modal' }
                    }));
                };

                const confirmDelBtn = document.getElementById('confirm-delete-api-btn');
                if (confirmDelBtn) {
                    confirmDelBtn.onclick = async function() {
                        if (!activeDeleteId) return;
                        this.disabled = true;
                        const originalText = this.innerHTML;
                        this.innerHTML = `<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>`;

                        try {
                            const response = await fetch(`/merchant/settings/api-keys/${activeDeleteId}/delete`, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                            const result = await response.json();
                            if (result.status === 'true') {
                                showToast('success', result.message);
                                window.dispatchEvent(new CustomEvent('close-modal', {
                                    detail: { id: 'delete-api-modal' }
                                }));
                                loadApiKeys(currentPage);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'Deletion failed.');
                        } finally {
                            this.disabled = false;
                            this.innerHTML = originalText;
                            activeDeleteId = null;
                        }
                    };
                }

                // Set up limits & status change events
                const showLimitSelect = document.getElementById('show-limit');
                if (showLimitSelect) showLimitSelect.onchange = () => loadApiKeys(1);

                const filterStatusSelect = document.getElementById('filter-status');
                if (filterStatusSelect) filterStatusSelect.onchange = () => loadApiKeys(1);

                // Initial Load
                loadApiKeys();
            })();
        </script>
    </div>
@endsection
