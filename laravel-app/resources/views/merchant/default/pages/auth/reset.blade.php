@extends('m::layouts.auth')

@section('title', 'Reset Password')

@section('content')

    <div class="flex flex-col flex-1 w-full lg:w-1/2">
        <div class="w-full max-w-md pt-10 mx-auto">
            <a href="{{ route('merchant.login') }}"
                class="inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg class="stroke-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                    fill="none">
                    <path d="M12.7083 5L7.5 10.2083L12.7083 15.4167" stroke="" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Back to Login
            </a>
        </div>
        <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
            <div>
                <div class="mb-5 sm:mb-8">

                    {{-- show logo only for mobile. --}}
                    <div class="sm:hidden mobile-only-logo">
                        <img src="{{ asset('assets/images/favicon-dark.png') }}" alt="{{ config('app.name') }} Logo"
                            class="w-12 h-12 rounded-xl mb-6">
                    </div>

                    <h1 class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md">
                        Reset Password
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Create a new password for your account.
                    </p>
                </div>
                <div>

                    <form action="{{ route('merchant.password.update') }}" method="POST" x-data="{
                        loading: false,
                        error: '',
                        submitForm() {
                            this.loading = true;
                            this.error = '';

                            fetch(this.$el.action, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        token: this.$refs.token.value,
                                        email: this.$refs.email.value,
                                        password: this.$refs.password.value,
                                        password_confirmation: this.$refs.password_confirmation.value
                                    })
                                })
                                .then(response => response.json().then(data => ({ status: response.status, data })))
                                .then(({ status, data }) => {
                                    this.loading = false;
                                    if (status === 200) {
                                        this.$dispatch('open-modal', {
                                            id: 'success-modal',
                                            message: data.message
                                        });
                                    } else {
                                        this.error = data.message || 'Something went wrong. Please try again.';
                                    }
                                })
                                .catch(err => {
                                    this.loading = false;
                                    this.error = 'Connection error. Please try again.';
                                });
                        }
                    }" @submit.prevent="submitForm">
                        @csrf



                        <!-- Token (Hidden) -->
                        <input type="hidden" name="token" value="{{ $token }}" x-ref="token">

                        <div class="space-y-5">
                            <!-- Email -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Email Address <span class="text-error-500">*</span>
                                </label>
                                <input type="email" id="email" name="email" value="{{ request()->email }}" readonly
                                    x-ref="email" placeholder="Enter your email address"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 shadow-theme-xs cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-white/50" />
                            </div>

                            <div x-show="error" x-transition
                                class="rounded-xl border border-error-500 bg-error-50 p-4 dark:border-error-500/30 dark:bg-error-500/15">
                                <div class="flex items-start gap-3">
                                    <div class="-mt-0.5 text-error-500">
                                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M20.3499 12.0004C20.3499 16.612 16.6115 20.3504 11.9999 20.3504C7.38832 20.3504 3.6499 16.612 3.6499 12.0004C3.6499 7.38881 7.38833 3.65039 11.9999 3.65039C16.6115 3.65039 20.3499 7.38881 20.3499 12.0004ZM11.9999 22.1504C17.6056 22.1504 22.1499 17.6061 22.1499 12.0004C22.1499 6.3947 17.6056 1.85039 11.9999 1.85039C6.39421 1.85039 1.8499 6.3947 1.8499 12.0004C1.8499 17.6061 6.39421 22.1504 11.9999 22.1504ZM13.0008 16.4753C13.0008 15.923 12.5531 15.4753 12.0008 15.4753L11.9998 15.4753C11.4475 15.4753 10.9998 15.923 10.9998 16.4753C10.9998 17.0276 11.4475 17.4753 11.9998 17.4753L12.0008 17.4753C12.5531 17.4753 13.0008 17.0276 13.0008 16.4753ZM11.9998 6.62898C12.414 6.62898 12.7498 6.96476 12.7498 7.37898L12.7498 13.0555C12.7498 13.4697 12.414 13.8055 11.9998 13.8055C11.5856 13.8055 11.2498 13.4697 11.2498 13.0555L11.2498 7.37898C11.2498 6.96476 11.5856 6.62898 11.9998 6.62898Z"
                                                fill="#F04438"></path>
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-800 dark:text-white/60" x-text="error"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    New Password <span class="text-error-500">*</span>
                                </label>
                                <input type="password" name="password" required x-ref="password"
                                    placeholder="Enter new password"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    Confirm Password <span class="text-error-500">*</span>
                                </label>
                                <input type="password" name="password_confirmation" required x-ref="password_confirmation"
                                    placeholder="Confirm new password"
                                    class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                            </div>

                            <div>
                                <button type="submit" :disabled="loading"
                                    class="flex w-full items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition-colors hover:bg-brand-600 disabled:opacity-50">
                                    <template x-if="!loading">
                                        <span>Reset Password</span>
                                    </template>
                                    <template x-if="loading">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="animate-spin">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <circle opacity="0.5" cx="10" cy="10" r="8.75" stroke="white"
                                                        stroke-width="2.5"></circle>
                                                    <mask id="path-2-inside-1_3755_26472" fill="white">
                                                        <path
                                                            d="M18.2372 12.9506C18.8873 13.1835 19.6113 12.846 19.7613 12.1719C20.0138 11.0369 20.0672 9.86319 19.9156 8.70384C19.7099 7.12996 19.1325 5.62766 18.2311 4.32117C17.3297 3.01467 16.1303 1.94151 14.7319 1.19042C13.7019 0.637155 12.5858 0.270357 11.435 0.103491C10.7516 0.00440265 10.179 0.561473 10.1659 1.25187V1.25187C10.1528 1.94226 10.7059 2.50202 11.3845 2.6295C12.1384 2.77112 12.8686 3.02803 13.5487 3.39333C14.5973 3.95661 15.4968 4.76141 16.1728 5.74121C16.8488 6.721 17.2819 7.84764 17.4361 9.02796C17.5362 9.79345 17.5172 10.5673 17.3819 11.3223C17.2602 12.002 17.5871 12.7178 18.2372 12.9506V12.9506Z">
                                                        </path>
                                                    </mask>
                                                    <path
                                                        d="M18.2372 12.9506C18.8873 13.1835 19.6113 12.846 19.7613 12.1719C20.0138 11.0369 20.0672 9.86319 19.9156 8.70384C19.7099 7.12996 19.1325 5.62766 18.2311 4.32117C17.3297 3.01467 16.1303 1.94151 14.7319 1.19042C13.7019 0.637155 12.5858 0.270357 11.435 0.103491C10.7516 0.00440265 10.179 0.561473 10.1659 1.25187V1.25187C10.1528 1.94226 10.7059 2.50202 11.3845 2.6295C12.1384 2.77112 12.8686 3.02803 13.5487 3.39333C14.5973 3.95661 15.4968 4.76141 16.1728 5.74121C16.8488 6.721 17.2819 7.84764 17.4361 9.02796C17.5362 9.79345 17.5172 10.5673 17.3819 11.3223C17.2602 12.002 17.5871 12.7178 18.2372 12.9506V12.9506Z"
                                                        stroke="white" stroke-width="4"
                                                        mask="url(#path-2-inside-1_3755_26472)">
                                                    </path>
                                                </svg>
                                            </span>
                                            <span>Loading...</span>
                                        </div>
                                    </template>
                                </button>
                            </div>
                        </div>

                        {{-- Success Modal --}}
                        <x-m::modal
                            id="success-modal"
                            type="brand"
                            title="Password Changed!"
                            actionTitle="Proceed to Login"
                            :actionRoute="route('merchant.login')"
                            :isDispose="false"
                        >
                            <x-slot name="icon">
                                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-success-50 text-success-500 dark:bg-success-500/10 mb-6">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </x-slot>
                        </x-m::modal>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
