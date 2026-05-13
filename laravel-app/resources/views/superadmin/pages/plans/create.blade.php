@extends('superadmin.layouts.app')

@section('title', 'Create Subscription Plan')

@section('content')

    <div class="kt-container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-medium leading-none text-mono">
                    Create New Subscription Plan
                </h1>
                <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                    Define a new tier with custom pricing and module-level access
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <a class="kt-btn kt-btn-outline" href="{{ route('superadmin.plans.index') }}">
                    <i class="ki-filled ki-arrow-left"></i>
                    Back to Plans
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-danger/10 border border-danger/20 text-danger p-4 rounded-lg mb-5">
                <ul class="list-disc list-inside text-sm font-medium">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="kt-container-fixed">
        <form action="{{ route('superadmin.plans.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
                <!-- Left Column: General & Pricing -->
                <div class="lg:col-span-2 flex flex-col gap-5 lg:gap-7.5">
                    <!-- General Settings -->
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">General Settings</h3>
                        </div>
                        <div class="kt-card-content grid gap-5 pt-4">
                            <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="kt-form-label max-w-48 w-full">Plan Name <span class="text-danger">*</span></label>
                                <div class="grow">
                                    <input class="kt-input w-full" name="name" value="{{ old('name') }}" placeholder="e.g. Professional Tier" type="text" id="plan_name" required>
                                </div>
                            </div>
                            <div class="flex items-center flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="kt-form-label max-w-48 w-full">Plan Slug <span class="text-danger">*</span></label>
                                <div class="grow">
                                    <input class="kt-input w-full" name="slug" value="{{ old('slug') }}" placeholder="e.g. professional-tier" type="text" id="plan_slug" required>
                                </div>
                            </div>
                            <div class="flex items-baseline flex-wrap lg:flex-nowrap gap-2.5">
                                <label class="kt-form-label max-w-48 w-full">Description</label>
                                <div class="grow">
                                    <textarea class="kt-input w-full min-h-[100px]" name="description" placeholder="Briefly describe what this plan offers...">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Module Access Control -->
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Module-Based Access</h3>
                        </div>
                        <div class="kt-card-content pt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                @php
                                    $modules = [
                                        ['key' => 'invoices', 'label' => 'Invoices & Billing', 'icon' => 'ki-bill', 'desc' => 'Allow generating and sending professional invoices.'],
                                        ['key' => 'payment_links', 'label' => 'Payment Links', 'icon' => 'ki-link', 'desc' => 'Allow creating standalone payment pages.'],
                                        ['key' => 'api_access', 'label' => 'API Access', 'icon' => 'ki-code', 'desc' => 'Enable developer API and Webhook integrations.'],
                                        ['key' => 'settlements', 'label' => 'Settlements', 'icon' => 'ki-wallet', 'desc' => 'Allow automated and manual settlement requests.'],
                                        ['key' => 'multi_currency', 'label' => 'Multi-Currency', 'icon' => 'ki-bitcoin', 'desc' => 'Accept payments in multiple regional currencies.'],
                                        ['key' => 'custom_branding', 'label' => 'Custom Branding', 'icon' => 'ki-brush', 'desc' => 'Remove platform branding and use custom themes.'],
                                        ['key' => 'priority_support', 'label' => 'Priority Support', 'icon' => 'ki-messages', 'desc' => 'Provide 24/7 priority support channels.'],
                                        ['key' => 'advanced_reports', 'label' => 'Advanced Reporting', 'icon' => 'ki-chart-line-star', 'desc' => 'Access to detailed financial analytics and exports.'],
                                    ];
                                @endphp

                                @foreach($modules as $module)
                                    <div class="flex items-start gap-4 p-4 rounded-xl border border-border bg-muted/20 hover:bg-muted/40 transition-colors">
                                        <div class="size-10 rounded-lg bg-background flex items-center justify-center border border-border shrink-0">
                                            <i class="ki-filled {{ $module['icon'] }} text-xl text-primary"></i>
                                        </div>
                                        <div class="grow">
                                            <div class="flex justify-between items-center mb-1">
                                                <label for="feat_{{ $module['key'] }}" class="text-sm font-bold text-foreground cursor-pointer">{{ $module['label'] }}</label>
                                                <label class="kt-switch kt-switch-sm">
                                                    <input type="checkbox" name="features[{{ $module['key'] }}]" id="feat_{{ $module['key'] }}" {{ old('features.'.$module['key']) ? 'checked' : '' }}>
                                                </label>
                                            </div>
                                            <p class="text-xs text-muted-foreground leading-normal">
                                                {{ $module['desc'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Pricing & Status -->
                <div class="flex flex-col gap-5 lg:gap-7.5">
                    <!-- Pricing Card -->
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Pricing & Billing</h3>
                        </div>
                        <div class="kt-card-content grid gap-5 pt-4">
                            <div class="flex flex-col gap-2">
                                <label class="kt-form-label">Price <span class="text-danger">*</span></label>
                                <div class="relative">
                                    <input class="kt-input w-full ps-12" name="price" value="{{ old('price', '0.00') }}" type="number" step="0.01" required>
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-4 pointer-events-none border-e border-border me-3">
                                        <span class="text-sm font-bold text-muted-foreground">USD</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="kt-form-label">Currency Code</label>
                                <input class="kt-input w-full uppercase" name="currency" value="{{ old('currency', 'USD') }}" type="text" maxlength="3">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="kt-form-label">Billing Interval <span class="text-danger">*</span></label>
                                <select class="kt-input w-full" name="interval">
                                    <option value="month" {{ old('interval') == 'month' ? 'selected' : '' }}>Monthly</option>
                                    <option value="year" {{ old('interval') == 'year' ? 'selected' : '' }}>Yearly</option>
                                    <option value="lifetime" {{ old('interval') == 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Options -->
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Status & Options</h3>
                        </div>
                        <div class="kt-card-content grid gap-5 pt-4">
                            <div class="flex items-center justify-between p-3 rounded-lg border border-border bg-muted/10">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-foreground">Active Status</span>
                                    <span class="text-xs text-muted-foreground">Plan will be available for subscription.</span>
                                </div>
                                <label class="kt-switch kt-switch-sm">
                                    <input type="checkbox" name="is_active" checked value="1" {{ old('is_active') ? 'checked' : '' }}>
                                </label>
                            </div>

                            <div class="flex items-center justify-between p-3 rounded-lg border border-border bg-muted/10">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-foreground">Default Plan</span>
                                    <span class="text-xs text-muted-foreground">Automatically assigned to new merchants.</span>
                                </div>
                                <label class="kt-switch kt-switch-sm">
                                    <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="kt-btn kt-btn-primary w-full py-4 text-base shadow-lg shadow-primary/20">
                        <i class="ki-filled ki-check-circle text-lg"></i>
                        Save & Create Plan
                    </button>
                    <a href="{{ route('superadmin.plans.index') }}" class="kt-btn kt-btn-outline w-full">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Auto-slugify
        document.getElementById('plan_name').addEventListener('input', function() {
            let slug = this.value.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('plan_slug').value = slug;
        });
    </script>

@endsection
