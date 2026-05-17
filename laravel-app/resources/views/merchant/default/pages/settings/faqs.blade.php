@extends('merchant.default.layouts.app')

@section('title', 'FAQ Settings')

@section('content')
    <div id="settings-container">
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `FAQ Settings` }">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">FAQ Settings</h2>
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
                            <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">FAQ Settings</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Breadcrumb End -->

            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                <!-- Card Header Start -->
                <div class="px-4 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Frequently Asked Questions</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Manage FAQs that will be displayed to customers on checkout pages.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'create-faq-modal' } }))"
                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add New FAQ
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
                                    <option value="10">10</option>
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
                                    <option value="">All</option>
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
                                    <option value="active">Activate Selected</option>
                                    <option value="inactive">Deactivate Selected</option>
                                    <option value="delete">Delete Selected</option>
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
                        <input type="text" id="search-input" placeholder="Search FAQs..."
                            oninput="debouncedLoadFaqs()"
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
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Question</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-left">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Answer Preview</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-center">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Status</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-left">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Last Updated</p>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap text-right">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">Actions</p>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="faq-table-body" class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr>
                                <td colspan="6" class="px-5 py-20 text-center">
                                    <x-loader show-text="true" text="Fetching brand FAQs..." />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Table Area End -->

                <!-- Table Footer Start -->
                <div class="px-4 py-4 sm:px-6 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div id="datatable-info" class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Showing 0 to 0 of 0 entries
                        </div>
                        <div id="pagination-container">
                            <!-- Pagination will be loaded via AJAX -->
                        </div>
                    </div>
                </div>
                <!-- Table Footer End -->
            </div>
        </div>

        <!-- Create FAQ Modal -->
        <x-m::modal id="create-faq-modal" type="brand" title="Create New FAQ"
            description="Add a new frequently asked question for the brand checkout screen." actionTitle="Create FAQ"
            actionId="submit-create-faq-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
            </x-slot>

            <form id="create-faq-form" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Question / Title</label>
                    <input type="text" name="faq_title" placeholder="How long does delivery take?" required
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Answer / Description</label>
                    <textarea name="faq_description" rows="4" placeholder="Delivery typically takes 3-5 business days depending on location." required
                        class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                    <div class="relative bg-transparent">
                        <select name="faq_status" required
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

        <!-- Edit FAQ Modal -->
        <x-m::modal id="edit-faq-modal" type="brand" title="Edit FAQ"
            description="Modify the existing frequently asked question." actionTitle="Save Changes"
            actionId="submit-edit-faq-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
            </x-slot>

            <form id="edit-faq-form" class="space-y-4">
                @csrf
                <input type="hidden" name="faq_id" id="edit_faq_id">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Question / Title</label>
                    <input type="text" name="faq_title" id="edit_faq_title" placeholder="How long does delivery take?" required
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Answer / Description</label>
                    <textarea name="faq_description" id="edit_faq_description" rows="4" required
                        class="dark:bg-dark-900 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                    <div class="relative bg-transparent">
                        <select name="faq_status" id="edit_faq_status" required
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

        <!-- Delete FAQ Modal -->
        <x-m::modal id="delete-faq-modal" type="brand" title="Delete FAQ"
            description="Are you sure you want to delete this FAQ? This action cannot be undone." actionTitle="Delete FAQ"
            actionId="confirm-delete-faq-btn" :cancelButtonShow="true" :isDispose="true">
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
            description="Are you sure you want to apply this bulk action to the selected FAQs?"
            actionTitle="Apply Action" actionId="confirm-bulk-action-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            </x-slot>
        </x-m::modal>

        <script>
            (function() {
                let currentPage = 1;
                let activeDeleteId = null;

                // Simple debounce implementation
                function debounce(func, wait) {
                    let timeout;
                    return function(...args) {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => func.apply(this, args), wait);
                    };
                }

                window.loadFaqs = async function(page = 1) {
                    currentPage = page;
                    const tableBody = document.getElementById('faq-table-body');
                    const searchVal = document.querySelector('#settings-container #search-input')?.value || '';
                    const limitVal = document.getElementById('show-limit')?.value || 10;
                    const statusVal = document.getElementById('filter-status')?.value || '';

                    tableBody.innerHTML =
                        `<tr><td colspan="6" class="px-5 py-20 text-center"><x-loader show-text="true" text="Fetching brand FAQs..." /></td></tr>`;

                    try {
                        const params = new URLSearchParams({
                            page: page,
                            search_input: searchVal,
                            show_limit: limitVal,
                            filter_status: statusVal
                        });

                        const fullUrl = `{{ route('merchant.settings.faqs') }}?${params.toString()}`;
                        const response = await fetch(fullUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();

                        if (data.status === 'true') {
                            let html = '';
                            if (data.response.length === 0) {
                                html =
                                    `<tr><td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">No FAQs found.</td></tr>`;
                            } else {
                                data.response.forEach(item => {
                                    const statusBadge = item.status === 'active'
                                        ? '<span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-400">Active</span>'
                                        : '<span class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-semibold text-gray-600 dark:bg-white/5 dark:text-gray-400">Inactive</span>';

                                    html += `
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all border-b border-gray-100 dark:border-gray-800 last:border-0">
                                    <td class="px-4 py-4 w-10">
                                        <div class="flex items-center">
                                            <input type="checkbox" class="faq-row-checkbox h-4 w-4 rounded-sm border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 transition" value="${item.id}">
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-white/90">${item.title}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-[280px] truncate">${item.description}</p>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        ${statusBadge}
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        ${item.updated_date}
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
                                                        <button @click="openDropDown = false; editFaq(${item.id})"
                                                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                                                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                            Edit FAQ
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button @click="openDropDown = false; deleteFaqPrompt(${item.id})"
                                                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                                                            <svg class="h-5 w-5 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                            Delete FAQ
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>`;
                                });
                            }
                            tableBody.innerHTML = html;
                            document.getElementById('datatable-info').innerHTML = data.datatableInfo;
                            document.getElementById('pagination-container').innerHTML = data.pagination;

                            document.querySelectorAll('#pagination-container button[data-page]').forEach(btn => {
                                btn.onclick = () => loadFaqs(btn.dataset.page);
                            });

                            // Re-bind row checkbox triggers
                            bindCheckboxes();

                            // Re-initialize Alpine for dropdowns
                            if (window.Alpine) {
                                Alpine.initTree(tableBody);
                            }
                        }
                    } catch (error) {
                        console.error('Fetch error:', error);
                        tableBody.innerHTML =
                            `<tr><td colspan="6" class="px-5 py-10 text-center text-red-500 font-medium">Failed to load FAQs. Please try again.</td></tr>`;
                    }
                }

                window.debouncedLoadFaqs = debounce(() => loadFaqs(1), 500);

                // Row selection & bulk actions logic
                function bindCheckboxes() {
                    const selectAll = document.getElementById('select-all-checkbox');
                    const checkboxes = document.querySelectorAll('.faq-row-checkbox');
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
                            // Update selectAll status
                            if (selectAll) {
                                selectAll.checked = [...checkboxes].every(c => c.checked);
                            }
                            updateBulkContainer();
                        };
                    });

                    function updateBulkContainer() {
                        const selected = document.querySelectorAll('.faq-row-checkbox:checked');
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

                // Bulk Action submit
                let activeBulkAction = null;
                let activeBulkIds = [];

                const applyBulkBtn = document.getElementById('apply-bulk-action-btn');
                if (applyBulkBtn) {
                    applyBulkBtn.onclick = function() {
                        const action = document.getElementById('bulk-action-select').value;
                        if (!action) {
                            showToast('error', 'Please select a bulk action.');
                            return;
                        }

                        const selectedIds = [...document.querySelectorAll('.faq-row-checkbox:checked')].map(chk => chk.value);
                        if (selectedIds.length === 0) {
                            showToast('error', 'No FAQs selected.');
                            return;
                        }

                        activeBulkAction = action;
                        activeBulkIds = selectedIds;

                        let actionLabel = action === 'delete' ? 'delete' : (action === 'active' ? 'activate' : 'deactivate');
                        let customMsg = `Are you sure you want to ${actionLabel} the ${selectedIds.length} selected FAQs?`;

                        window.dispatchEvent(new CustomEvent('open-modal', {
                            detail: {
                                id: 'bulk-action-modal',
                                title: 'Confirm Bulk Action',
                                message: customMsg
                            }
                        }));
                    };
                }

                const confirmBulkActionBtn = document.getElementById('confirm-bulk-action-btn');
                if (confirmBulkActionBtn) {
                    confirmBulkActionBtn.onclick = async function() {
                        if (!activeBulkAction || activeBulkIds.length === 0) return;

                        const originalText = confirmBulkActionBtn.innerHTML;
                        confirmBulkActionBtn.disabled = true;
                        confirmBulkActionBtn.innerHTML = `<svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Applying...`;

                        try {
                            const response = await fetch("{{ route('merchant.settings.faqs.bulk') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    action: activeBulkAction,
                                    ids: activeBulkIds
                                })
                            });
                            const result = await response.json();
                            if (result.status === 'true') {
                                showToast('success', result.message);
                                window.dispatchEvent(new CustomEvent('close-modal', {
                                    detail: { id: 'bulk-action-modal' }
                                }));
                                // Clear checkboxes
                                const mainCheckbox = document.getElementById('select-all-faqs');
                                if (mainCheckbox) mainCheckbox.checked = false;
                                document.querySelectorAll('.faq-row-checkbox').forEach(chk => chk.checked = false);
                                const bulkContainer = document.getElementById('bulk-actions-container');
                                if (bulkContainer) {
                                    bulkContainer.classList.add('hidden');
                                    bulkContainer.classList.remove('flex');
                                }
                                loadFaqs(currentPage);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'Bulk action failed');
                        } finally {
                            confirmBulkActionBtn.disabled = false;
                            confirmBulkActionBtn.innerHTML = originalText;
                        }
                    };
                }

                // Create FAQ logic
                const cBtn = document.getElementById('submit-create-faq-btn');
                if (cBtn) {
                    cBtn.onclick = function() {
                        document.getElementById('create-faq-form').requestSubmit();
                    };
                }

                const cForm = document.getElementById('create-faq-form');
                if (cForm) {
                    cForm.onsubmit = async function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        try {
                            const response = await fetch("{{ route('merchant.settings.faqs.create') }}", {
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
                                    detail: { id: 'create-faq-modal' }
                                }));
                                cForm.reset();
                                loadFaqs(1);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'FAQ creation failed.');
                        }
                    };
                }

                // Edit FAQ logic
                window.editFaq = async function(id) {
                    try {
                        const response = await fetch(`/merchant/settings/faqs/${id}/info`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();

                        if (data.status === 'true') {
                            document.getElementById('edit_faq_id').value = id;
                            document.getElementById('edit_faq_title').value = data.title;
                            document.getElementById('edit_faq_description').value = data.description;
                            document.getElementById('edit_faq_status').value = data.fstatus;

                            window.dispatchEvent(new CustomEvent('open-modal', {
                                detail: { id: 'edit-faq-modal' }
                            }));
                        } else {
                            showToast('error', 'Unable to fetch FAQ details.');
                        }
                    } catch (error) {
                        showToast('error', 'Error occurred.');
                    }
                }

                const eBtn = document.getElementById('submit-edit-faq-btn');
                if (eBtn) {
                    eBtn.onclick = function() {
                        document.getElementById('edit-faq-form').requestSubmit();
                    };
                }

                const eForm = document.getElementById('edit-faq-form');
                if (eForm) {
                    eForm.onsubmit = async function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        try {
                            const response = await fetch("{{ route('merchant.settings.faqs.edit') }}", {
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
                                    detail: { id: 'edit-faq-modal' }
                                }));
                                loadFaqs(currentPage);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'FAQ update failed.');
                        }
                    };
                }

                // Delete FAQ logic
                window.deleteFaqPrompt = function(id) {
                    activeDeleteId = id;
                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: { id: 'delete-faq-modal' }
                    }));
                };

                const confirmDelBtn = document.getElementById('confirm-delete-faq-btn');
                if (confirmDelBtn) {
                    confirmDelBtn.onclick = async function() {
                        if (!activeDeleteId) return;
                        this.disabled = true;
                        const originalText = this.innerHTML;
                        this.innerHTML = `<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>`;

                        try {
                            const response = await fetch(`/merchant/settings/faqs/${activeDeleteId}/delete`, {
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
                                    detail: { id: 'delete-faq-modal' }
                                }));
                                loadFaqs(currentPage);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'Delete failed.');
                        } finally {
                            this.disabled = false;
                            this.innerHTML = originalText;
                            activeDeleteId = null;
                        }
                    };
                }

                // Filters change listeners
                const showLimitSelect = document.getElementById('show-limit');
                if (showLimitSelect) {
                    showLimitSelect.onchange = () => loadFaqs(1);
                }

                const filterStatusSelect = document.getElementById('filter-status');
                if (filterStatusSelect) {
                    filterStatusSelect.onchange = () => loadFaqs(1);
                }

                loadFaqs();
            })();
        </script>
    </div>
@endsection
