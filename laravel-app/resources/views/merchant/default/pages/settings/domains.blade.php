@extends('merchant.default.layouts.app')

@section('title', 'Whitelisted Domains')

@section('content')
    <div id="settings-container">
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `Whitelisted Domains` }">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">Whitelisted Domains</h2>
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
                            <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">Whitelisted Domains</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Breadcrumb End -->

            <!-- Main Domains Card Start -->
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                <!-- Card Header Start -->
                <div class="px-4 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Whitelisted Checkout Domains</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Whitelist your target e-commerce store domain URLs to secure hosted redirection checks.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'create-domain-modal' } }))"
                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Whitelist Domain
                        </button>
                    </div>
                </div>
                <!-- Card Header End -->

                <!-- Data Table Filter Start -->
                <div class="mb-4 flex flex-col gap-2 px-4 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-gray-500 dark:text-gray-400"> Show </span>
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select id="show-limit"
                                class="dark:bg-dark-900 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pl-3 pr-8 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="10">10</option>
                                <option value="8" selected>8</option>
                                <option value="5">5</option>
                            </select>
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </div>
                        <span class="text-gray-500 dark:text-gray-400 font-medium"> entries </span>

                        <!-- Bulk Actions -->
                        <div id="bulk-action-wrapper" class="hidden items-center gap-2 pl-4 border-l border-gray-200 dark:border-gray-700">
                            <select id="bulk-action-select"
                                class="dark:bg-dark-900 h-9 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">Bulk Actions</option>
                                <option value="activated">Activate</option>
                                <option value="inactive">Deactivate</option>
                                <option value="deleted">Delete</option>
                            </select>
                            <button onclick="triggerBulkAction()"
                                class="h-9 px-4 rounded-lg bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                                Apply
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent w-[140px]">
                            <select id="filter-status"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent py-2 pl-3 pr-8 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </div>

                        <div class="relative">
                            <button class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z"></path>
                                </svg>
                            </button>
                            <input type="text" id="search-input" placeholder="Search Domain URL..." oninput="debouncedLoadDomains()"
                                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 xl:w-[250px]">
                        </div>
                    </div>
                </div>
                <!-- Data Table Filter End -->

                <!-- Table Area Start -->
                <div class="custom-scrollbar max-w-full overflow-x-auto overflow-y-visible px-5 sm:px-6">
                    <table class="min-w-full">
                        <thead class="border-y border-gray-100 py-3 dark:border-gray-800">
                            <tr>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-left w-1">
                                    <input type="checkbox" id="select-all-checkbox" onclick="toggleSelectAll(this)"
                                        class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-left">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Domain URL</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-left">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Created / Updated</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-left">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Status</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-right">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Actions</p>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="domain-table-body" class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr>
                                <td colspan="5" class="px-5 py-20 text-center">
                                    <x-loader show-text="true" text="Loading whitelisted domains..." />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Table Area End -->

                <!-- Table Footer Start -->
                <div class="flex flex-col items-center justify-between gap-4 border-t border-gray-100 p-5 dark:border-gray-800 sm:flex-row sm:px-6">
                    <p id="table-info" class="text-sm text-gray-500 dark:text-gray-400">Showing 0 to 0 of 0 entries</p>
                    <div id="table-pagination"></div>
                </div>
                <!-- Table Footer End -->
            </div>
            <!-- Main Domains Card End -->
        </div>

        <!-- Modals Layout Area -->

        <!-- Create Domain Modal -->
        <x-m::modal id="create-domain-modal" type="brand" title="Whitelist Domain"
            description="Add a new verified domain address to enable client-side redirection safety hooks." actionTitle="Whitelist"
            actionId="submit-create-domain-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                    </svg>
                </div>
            </x-slot>

            <form id="create-domain-form" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Domain URL <span class="text-danger">*</span></label>
                    <input type="text" name="domain_name" placeholder="https://myteststore.com" required
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <p class="mt-1 text-xs text-gray-400">URLs will automatically normalize to hostnames (e.g. myteststore.com).</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status <span class="text-danger">*</span></label>
                    <div class="relative bg-transparent">
                        <select name="domain_status" required
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

        <!-- Edit Domain Modal -->
        <x-m::modal id="edit-domain-modal" type="brand" title="Edit Domain"
            description="Modify whitelisted address string or change its verification status." actionTitle="Save Changes"
            actionId="submit-edit-domain-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
            </x-slot>

            <form id="edit-domain-form" class="space-y-4">
                @csrf
                <input type="hidden" name="domain_id" id="edit_domain_id">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Domain URL <span class="text-danger">*</span></label>
                    <input type="text" name="domain_name" id="edit_domain_name" required
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status <span class="text-danger">*</span></label>
                    <div class="relative bg-transparent">
                        <select name="domain_status" id="edit_domain_status" required
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

        <!-- Delete Domain Modal -->
        <x-m::modal id="delete-domain-modal" type="brand" title="Delete Whitelisted Domain"
            description="Are you sure you want to remove this domain from whitelist? Hosted checkouts redirecting to this site may stop resolving safely." actionTitle="Delete Domain"
            actionId="confirm-delete-domain-btn" :cancelButtonShow="true" :isDispose="true">
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
            description="Are you sure you want to apply this bulk status action to all selected domains?"
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
            (function() {
                let currentPage = 1;
                let activeDeleteId = null;
                let activeBulkAction = null;
                let searchTimeout = null;

                // Load domains list
                window.loadDomains = async function(page = 1) {
                    currentPage = page;
                    const limit = document.getElementById('show-limit')?.value || 8;
                    const search = document.getElementById('search-input')?.value || '';
                    const status = document.getElementById('filter-status')?.value || '';

                    const tbody = document.getElementById('domain-table-body');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-5 py-20 text-center">
                                <x-loader show-text="true" text="Loading whitelisted domains..." />
                            </td>
                        </tr>
                    `;

                    try {
                        const response = await fetch("{{ route('merchant.settings.domains.list') }}", {
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
                                let badgeClass = item.status === 'active' ? 'text-green-600 bg-green-50 dark:bg-green-500/10' : 'text-gray-500 bg-gray-50 dark:bg-gray-500/10';

                                rowsHtml += `
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <input type="checkbox" value="${item.id}" onchange="updateBulkActionState()"
                                                class="row-checkbox h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700">
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap font-medium text-gray-800 dark:text-white/90">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                                </svg>
                                                <span>${item.domain}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <div class="font-medium">${item.created_date}</div>
                                            <div class="text-xs text-gray-400">Updated: ${item.updated_date}</div>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold ${badgeClass}">
                                                <span class="w-1.5 h-1.5 rounded-full fill-current"></span>
                                                ${item.status.toUpperCase()}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap text-right">
                                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                                <button @click="open = !open" @click.away="open = false"
                                                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                                                    Actions
                                                    <svg class="w-4 h-4 ml-1.5 stroke-current" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    </svg>
                                                </button>
                                                <div x-show="open" style="display: none;"
                                                    class="absolute right-0 mt-1 w-[160px] origin-top-right rounded-lg bg-white p-1.5 shadow-lg ring-1 ring-black/5 dark:bg-gray-900 z-50">
                                                    <button onclick="editDomain('${item.id}')"
                                                        class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                                                        Edit Domain
                                                    </button>
                                                    <button onclick="deleteDomainPrompt('${item.id}')"
                                                        class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                                                        Delete Domain
                                                    </button>
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
                                    loadDomains(parseInt(this.dataset.page));
                                };
                            });
                        } else {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="5" class="px-5 py-20 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                            </svg>
                                            <span class="font-medium text-base">No whitelisted domains found</span>
                                            <span class="text-sm text-gray-400">Whitelist a checkout URL to ensure gateways redirect safely.</span>
                                        </div>
                                    </td>
                                </tr>
                            `;
                            document.getElementById('table-info').innerHTML = 'Showing 0 to 0 of 0 entries';
                            document.getElementById('table-pagination').innerHTML = '';
                        }
                    } catch (error) {
                        tbody.innerHTML = `<tr><td colspan="5" class="px-5 py-20 text-center text-red-600">Failed to load whitelisted domains. Please try again.</td></tr>`;
                    }

                    // Reset selection state
                    const selectAll = document.getElementById('select-all-checkbox');
                    if (selectAll) selectAll.checked = false;
                    updateBulkActionState();
                }

                // Debounce search inputs
                window.debouncedLoadDomains = function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        loadDomains(1);
                    }, 300);
                }

                // Selection check updates
                window.toggleSelectAll = function(el) {
                    document.querySelectorAll('.row-checkbox').forEach(cb => {
                        cb.checked = el.checked;
                    });
                    updateBulkActionState();
                }

                window.updateBulkActionState = function() {
                    const checked = document.querySelectorAll('.row-checkbox:checked').length;
                    const wrapper = document.getElementById('bulk-action-wrapper');
                    if (checked > 0) {
                        wrapper.classList.remove('hidden');
                        wrapper.classList.add('flex');
                    } else {
                        wrapper.classList.remove('flex');
                        wrapper.classList.add('hidden');
                    }
                }

                // Trigger Bulk Action Confirmation Modal
                window.triggerBulkAction = function() {
                    const action = document.getElementById('bulk-action-select').value;
                    if (!action) {
                        showToast('error', 'Please choose a bulk action to proceed.');
                        return;
                    }
                    activeBulkAction = action;
                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: { id: 'bulk-action-modal' }
                    }));
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
                            const response = await fetch("{{ route('merchant.settings.domains.bulk') }}", {
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
                                loadDomains(currentPage);
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

                // Whitelist Domain submit
                const cBtn = document.getElementById('submit-create-domain-btn');
                if (cBtn) {
                    cBtn.onclick = function() {
                        document.getElementById('create-domain-form').requestSubmit();
                    };
                }

                const cForm = document.getElementById('create-domain-form');
                if (cForm) {
                    cForm.onsubmit = async function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        try {
                            const response = await fetch("{{ route('merchant.settings.domains.create') }}", {
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
                                    detail: { id: 'create-domain-modal' }
                                }));
                                cForm.reset();
                                loadDomains(1);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'Whitelisting failed.');
                        }
                    };
                }

                // Edit Domain logic
                window.editDomain = async function(id) {
                    try {
                        const response = await fetch(`/merchant/settings/domains/${id}/info`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();

                        if (data.status === 'true') {
                            document.getElementById('edit_domain_id').value = id;
                            document.getElementById('edit_domain_name').value = data.domain;
                            document.getElementById('edit_domain_status').value = data.istatus;

                            window.dispatchEvent(new CustomEvent('open-modal', {
                                detail: { id: 'edit-domain-modal' }
                            }));
                        } else {
                            showToast('error', 'Unable to fetch details.');
                        }
                    } catch (error) {
                        showToast('error', 'Error occurred.');
                    }
                }

                const eBtn = document.getElementById('submit-edit-domain-btn');
                if (eBtn) {
                    eBtn.onclick = function() {
                        document.getElementById('edit-domain-form').requestSubmit();
                    };
                }

                const eForm = document.getElementById('edit-domain-form');
                if (eForm) {
                    eForm.onsubmit = async function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        try {
                            const response = await fetch("{{ route('merchant.settings.domains.edit') }}", {
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
                                    detail: { id: 'edit-domain-modal' }
                                }));
                                loadDomains(currentPage);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'Update failed.');
                        }
                    };
                }

                // Delete Domain logic
                window.deleteDomainPrompt = function(id) {
                    activeDeleteId = id;
                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: { id: 'delete-domain-modal' }
                    }));
                };

                const confirmDelBtn = document.getElementById('confirm-delete-domain-btn');
                if (confirmDelBtn) {
                    confirmDelBtn.onclick = async function() {
                        if (!activeDeleteId) return;
                        this.disabled = true;
                        const originalText = this.innerHTML;
                        this.innerHTML = `<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>`;

                        try {
                            const response = await fetch(`/merchant/settings/domains/${activeDeleteId}/delete`, {
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
                                    detail: { id: 'delete-domain-modal' }
                                }));
                                loadDomains(currentPage);
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
                if (showLimitSelect) showLimitSelect.onchange = () => loadDomains(1);

                const filterStatusSelect = document.getElementById('filter-status');
                if (filterStatusSelect) filterStatusSelect.onchange = () => loadDomains(1);

                // Initial Load
                loadDomains();
            })();
        </script>
    </div>
@endsection
