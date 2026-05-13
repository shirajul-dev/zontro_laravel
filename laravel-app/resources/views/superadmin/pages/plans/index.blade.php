@extends('superadmin.layouts.app')

@section('title', 'Subscription Plans')

@section('content')

    <div class="kt-container-fixed">
        <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
            <div class="flex flex-col justify-center gap-2">
                <h1 class="text-xl font-medium leading-none text-mono">
                    Plan Management
                </h1>
                <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                    Manage your subscription tiers and platform access levels
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <a class="kt-btn kt-btn-primary" href="{{ route('superadmin.plans.create') }}">
                    <i class="ki-filled ki-plus"></i>
                    Create New Plan
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-success/10 border border-success/20 text-success p-4 rounded-lg mb-5 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-danger/10 border border-danger/20 text-danger p-4 rounded-lg mb-5 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <div class="kt-container-fixed">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7.5">
            @forelse($plans as $plan)
                <div class="kt-card relative overflow-hidden group">
                    @if($plan->is_default)
                        <div class="absolute top-0 end-0 bg-primary text-white text-[10px] font-bold uppercase px-3 py-1 rounded-bl-lg z-10">
                            Default
                        </div>
                    @endif

                    <div class="kt-card-header border-0 pb-0">
                        <div class="flex flex-col gap-1">
                            <h3 class="text-lg font-bold text-foreground group-hover:text-primary transition-colors">
                                {{ $plan->name }}
                            </h3>
                            <span class="text-xs text-muted-foreground font-mono">{{ $plan->slug }}</span>
                        </div>
                        <div class="kt-badge kt-badge-sm {{ $plan->is_active ? 'kt-badge-outline kt-badge-success' : 'kt-badge-outline kt-badge-danger' }}">
                            {{ $plan->is_active ? 'Active' : 'Inactive' }}
                        </div>
                    </div>

                    <div class="kt-card-content pt-4">
                        <div class="flex items-baseline gap-1 mb-5">
                            <span class="text-3xl font-bold text-foreground tracking-tighter">{{ $plan->currency }} {{ number_format($plan->price, 2) }}</span>
                            <span class="text-sm font-medium text-muted-foreground">/ {{ $plan->interval }}</span>
                        </div>

                        <p class="text-sm text-secondary-foreground mb-6 line-clamp-2 h-10">
                            {{ $plan->description ?? 'No description provided for this plan tier.' }}
                        </p>

                        <div class="border-t border-dashed border-border pt-5">
                            <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-3">Key Features</h4>
                            <ul class="flex flex-col gap-2.5">
                                @php
                                    $allFeatures = [
                                        'invoices' => 'Invoices & Billing',
                                        'payment_links' => 'Payment Links',
                                        'api_access' => 'API & Webhooks',
                                        'settlements' => 'Settlements',
                                    ];
                                @endphp
                                @foreach($allFeatures as $key => $label)
                                    <li class="flex items-center gap-2 text-sm {{ $plan->hasFeature($key) ? 'text-foreground' : 'text-muted-foreground/50' }}">
                                        @if($plan->hasFeature($key))
                                            <i class="ki-filled ki-check-circle text-primary text-base"></i>
                                        @else
                                            <i class="ki-filled ki-cross-circle text-muted-foreground/30 text-base"></i>
                                        @endif
                                        {{ $label }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="kt-card-footer border-t border-border flex justify-between items-center gap-2 pt-4">
                        <a href="{{ route('superadmin.plans.edit', $plan->id) }}" class="kt-btn kt-btn-outline kt-btn-sm grow">
                            <i class="ki-filled ki-pencil"></i> Edit Plan
                        </a>
                        <form action="{{ route('superadmin.plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this plan?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="kt-btn kt-btn-icon kt-btn-outline kt-btn-sm text-danger hover:bg-danger/10 border-danger/20" {{ $plan->is_default ? 'disabled' : '' }}>
                                <i class="ki-filled ki-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="lg:col-span-3 kt-card p-20 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <i class="ki-filled ki-crown text-5xl text-muted-foreground/20"></i>
                        <h3 class="text-lg font-bold text-foreground">No Plans Created Yet</h3>
                        <p class="text-sm text-muted-foreground max-w-xs mx-auto">
                            Get started by creating your first subscription tier to define merchant access levels.
                        </p>
                        <a href="{{ route('superadmin.plans.create') }}" class="kt-btn kt-btn-primary mt-4">
                            <i class="ki-filled ki-plus"></i> Create Your First Plan
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

@endsection
