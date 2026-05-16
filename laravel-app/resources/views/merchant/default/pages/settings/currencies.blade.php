@extends('merchant.default.layouts.app')

@section('title', 'Currency Management')

@section('content')
    <div id="settings-container">
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 pb-20 md:p-6 md:pb-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `Currency Management` }">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageName">Currency Management
                    </h2>
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
                            <li class="text-sm text-gray-800 dark:text-white/90" x-text="pageName">Currency Management</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- Breadcrumb End -->

            <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] shadow-theme-xs">
                <!-- Card Header Start -->
                <div
                    class="px-4 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-800 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Currencies</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Manage and update your brand's currency exchange
                            rates.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">

                        <button type="button" id="sync-rates-btn"
                            class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Sync All Rates
                        </button>

                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'import-global-currency-modal' } }))"
                            class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Import Global
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
                                class="dark:bg-dark-900 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pl-3 pr-8 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                                @click="isOptionSelected = true">
                                <option value="10" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    10
                                </option>
                                <option value="8" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    8
                                </option>
                                <option value="5" class="text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                    5
                                </option>
                            </select>
                            <span class="absolute right-2 top-1/2 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke=""
                                        stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </div>
                        <span class="text-gray-500 dark:text-gray-400" style="padding-left: 10px"> entries </span>
                    </div>

                    <div class="relative">
                        <button type="button" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z"
                                    fill=""></path>
                            </svg>
                        </button>

                        <input type="text" id="search-input" placeholder="Search currencies..."
                            oninput="debouncedLoadCurrencies()"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 xl:w-[300px]">
                    </div>
                </div>
                <!-- Data Table Filter End -->

                <!-- Table Area Start -->
                <div class="custom-scrollbar max-w-full overflow-x-auto overflow-y-visible px-5 sm:px-6">
                    <table class="min-w-full">
                        <thead class="border-y border-gray-100 py-3 dark:border-gray-800">
                            <tr>
                                <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6">
                                    <div class="flex items-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Currency</p>
                                    </div>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6">
                                    <div class="flex items-center text-center justify-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400 text-center">Exchange Rate</p>
                                    </div>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6">
                                    <div class="flex items-center">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Last Sync</p>
                                    </div>
                                </th>
                                <th class="px-5 py-3 font-normal whitespace-nowrap sm:px-6">
                                    <div class="flex items-center justify-end">
                                        <p class="text-theme-sm text-gray-500 dark:text-gray-400">Actions</p>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="currency-table-body" class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr>
                                <td colspan="4" class="px-5 py-20 text-center">
                                    <x-loader show-text="true" text="Fetching currency data..." />
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

        <!-- Import Currency Modal -->
        <x-m::modal id="import-global-currency-modal" type="brand" title="Import Currencies"
            description="This will import all standard global currencies into your brand. Currencies already existing will be skipped."
            actionTitle="Import Now" actionId="confirm-import-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div
                    class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                </div>
            </x-slot>
        </x-m::modal>

        <!-- Edit Currency Modal -->
        <x-m::modal id="edit-currency-modal" type="brand" title="Edit Currency"
            description="Update the exchange rate and symbol for this currency." actionTitle="Save Changes"
            actionId="submit-edit-currency-btn" :cancelButtonShow="true" :isDispose="true">
            <x-slot name="icon">
                <div
                    class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/10 mb-6 mx-auto">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                </div>
            </x-slot>

            <form id="edit-currency-form" class="">
                @csrf
                <input type="hidden" name="currency_id" id="edit_currency_id">
                <div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Currency Code
                        </label>
                        <input type="text" id="edit_currency_code" readonly
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 shadow-theme-xs outline-hidden dark:border-gray-700 dark:bg-gray-800">
                    </div>

                    <div class="col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Symbol
                        </label>
                        <input type="text" name="currency_symbol" id="edit_currency_symbol" placeholder="$"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    </div>

                    <div class="col-span-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Rate (1 Base = ?)
                        </label>
                        <input type="number" step="any" name="currency_rate" id="edit_currency_rate" placeholder="1.00"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                    </div>
                </div>
            </form>
        </x-m::modal>

        <script>
            (function() {
                let currentPage = 1;

                // Simple debounce implementation
                function debounce(func, wait) {
                    let timeout;
                    return function(...args) {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => func.apply(this, args), wait);
                    };
                }

                window.loadCurrencies = async function(page = 1) {
                    currentPage = page;
                    const tableBody = document.getElementById('currency-table-body');
                    const searchVal = document.querySelector('#settings-container #search-input')?.value || '';
                    const limitVal = document.getElementById('show-limit')?.value || 10;

                    tableBody.innerHTML =
                        `<tr><td colspan="4" class="px-5 py-20 text-center"><x-loader show-text="true" text="Fetching currency data..." /></td></tr>`;

                    try {
                        const params = new URLSearchParams({
                            page: page,
                            search_input: searchVal,
                            show_limit: limitVal
                        });

                        const fullUrl = `{{ route('merchant.settings.currencies') }}?${params.toString()}`;
                        console.log('Fetching URL:', fullUrl);

                        const response = await fetch(fullUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();

                        console.log('Server confirmed search term:', data.search_term);

                        if (data.status === 'true') {
                            let html = '';
                            if (data.response.length === 0) {
                                html =
                                    `<tr><td colspan="4" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">No currencies found matching your search.</td></tr>`;
                            } else {
                                data.response.forEach(item => {
                                    const isBaseClass = item.is_base ? 'bg-brand-50/50 dark:bg-brand-500/5' : '';
                                    html += `
                                <tr class="${isBaseClass} hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-all border-b border-gray-100 dark:border-gray-800 last:border-0">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                ${item.code}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-800 dark:text-white/90">${item.code}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Symbol: ${item.symbol}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="text-sm font-medium text-gray-800 dark:text-white/90">${item.rate}</div>
                                        ${item.default === 'true' ? '<span class="inline-flex items-center rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/10 dark:text-success-400">Base Currency</span>' : ''}
                                    </td>
                                    <td class="px-5 py-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">${item.updated_date}</p>
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
                                                        <button @click="openDropDown = false; editCurrency(${JSON.stringify(item).replace(/"/g, '&quot;')})"
                                                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                                                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                            Edit Currency
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button @click="openDropDown = false; syncSingleRate('${item.id}')"
                                                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                                                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                            </svg>
                                                            Sync Rate
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
                                btn.onclick = () => loadCurrencies(btn.dataset.page);
                            });

                            // Re-initialize Alpine for the new dropdowns
                            if (window.Alpine) {
                                Alpine.initTree(tableBody);
                            }
                        }
                    } catch (error) {
                        console.error('Fetch error:', error);
                        tableBody.innerHTML =
                            `<tr><td colspan="4" class="px-5 py-10 text-center text-error-500 font-medium">Failed to load currencies. Please try again.</td></tr>`;
                    }
                }

                window.debouncedLoadCurrencies = debounce(() => loadCurrencies(1), 500);

                window.editCurrency = function(item) {
                    document.getElementById('edit_currency_id').value = item.id;
                    document.getElementById('edit_currency_code').value = item.code;
                    document.getElementById('edit_currency_symbol').value = item.symbol;

                    let rateValue = 0;
                    if (item.rate && item.rate.includes('=')) {
                        const rateStr = item.rate.split('=')[1].trim().split(' ')[0];
                        rateValue = parseFloat(rateStr.replace(/,/g, ''));
                    } else {
                        rateValue = parseFloat(item.rate);
                    }

                    document.getElementById('edit_currency_rate').value = rateValue;

                    window.dispatchEvent(new CustomEvent('open-modal', {
                        detail: {
                            id: 'edit-currency-modal'
                        }
                    }));
                }

                window.syncSingleRate = async function(id) {
                    try {
                        const response = await fetch("{{ route('merchant.settings.currencies.sync') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                ItemID: id
                            })
                        });
                        const result = await response.json();
                        if (result.status === 'true') {
                            showToast('success', result.message);
                            loadCurrencies(currentPage);
                        } else {
                            showToast('error', result.message);
                        }
                    } catch (error) {
                        showToast('error', 'Sync failed');
                    }
                }

                const lSelect = document.getElementById('show-limit');
                if (lSelect) {
                    lSelect.onchange = () => loadCurrencies(1);
                }

                const sBtn = document.getElementById('sync-rates-btn');
                if (sBtn) {
                    sBtn.onclick = async function() {
                        this.disabled = true;
                        const originalText = this.innerHTML;
                        this.innerHTML =
                            `<svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Syncing...`;

                        try {
                            const response = await fetch("{{ route('merchant.settings.currencies.sync') }}", {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                            const result = await response.json();
                            if (result.status === 'true') {
                                showToast('success', result.message);
                                loadCurrencies(1);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'Sync failed');
                        } finally {
                            this.disabled = false;
                            this.innerHTML = originalText;
                        }
                    };
                }

                const iBtn = document.getElementById('confirm-import-btn');
                if (iBtn) {
                    iBtn.onclick = async function() {
                        const btn = this;
                        btn.disabled = true;
                        const originalText = btn.innerHTML;
                        btn.innerHTML = `<svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Importing...`;

                        try {
                            const response = await fetch("{{ route('merchant.settings.currencies.import') }}", {
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
                                    detail: {
                                        id: 'import-global-currency-modal'
                                    }
                                }));
                                loadCurrencies(1);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'Import failed');
                        } finally {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    };
                }

                const eBtn = document.getElementById('submit-edit-currency-btn');
                if (eBtn) {
                    eBtn.onclick = function() {
                        document.getElementById('edit-currency-form').requestSubmit();
                    };
                }

                const eForm = document.getElementById('edit-currency-form');
                if (eForm) {
                    eForm.onsubmit = async function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        try {
                            const response = await fetch("{{ route('merchant.settings.currencies.update') }}", {
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
                                    detail: {
                                        id: 'edit-currency-modal'
                                    }
                                }));
                                loadCurrencies(currentPage);
                            } else {
                                showToast('error', result.message);
                            }
                        } catch (error) {
                            showToast('error', 'Update failed');
                        }
                    };
                }

                loadCurrencies();
            })();
        </script>

    </div>
@endsection
