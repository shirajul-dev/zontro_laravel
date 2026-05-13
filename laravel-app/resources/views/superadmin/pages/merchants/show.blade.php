@extends('superadmin.layouts.app')

@section('content')
<!-- Container -->
<div class="kt-container-fixed">
    <div class="flex flex-col gap-5 lg:gap-7.5">
        <!-- Profile Header (Team Crew Style) -->
        <div class="kt-card relative pb-10">
            <div class="kt-card-content lg:pt-12">
                <!-- Actions (Top Right) -->
                <div class="absolute top-5 end-5 flex gap-2">
                    <a href="{{ route('superadmin.merchants.edit', $merchant->a_id) }}" class="kt-btn kt-btn-outline kt-btn-xs sm:kt-btn-sm">
                        <i class="ki-filled ki-pencil"></i> Edit
                    </a>
                    @if($merchant->status == 'active')
                        <button onclick="openSuspendModal('{{ $merchant->a_id }}', '{{ $merchant->brand_name }}')" class="kt-btn kt-btn-danger kt-btn-xs sm:kt-btn-sm">
                            <i class="ki-filled ki-slash"></i> Suspend
                        </button>
                    @else
                        <button onclick="openReactivateModal('{{ $merchant->a_id }}', '{{ $merchant->brand_name }}')" class="kt-btn kt-btn-success kt-btn-xs sm:kt-btn-sm">
                            <i class="ki-filled ki-check"></i> Reactivate
                        </button>
                    @endif
                </div>

                <!-- Logo -->
                <div class="flex justify-center mb-5">
                    <div class="size-16 lg:size-24 relative">
                        @if($merchant->brand_logo)
                            <img class="rounded-full size-full object-contain p-1 border border-primary/20 bg-background" src="{{ asset($merchant->brand_logo) }}"/>
                        @else
                            <div class="rounded-full size-full bg-primary/10 flex items-center justify-center border border-primary/20">
                                <span class="text-3xl font-bold text-primary">{{ substr($merchant->brand_name ?? $merchant->full_name, 0, 1) }}</span>
                            </div>
                        @endif
                        <div class="flex size-3 bg-{{ $merchant->status == 'active' ? 'green' : 'red' }}-500 rounded-full absolute bottom-0.5 end-1 border-2 border-background"></div>
                    </div>
                </div>

                <!-- Name & Verification -->
                <div class="flex flex-col items-center gap-1.5 mb-5">
                    <div class="flex items-center justify-center gap-1.5">
                        <h1 class="text-2xl font-bold text-foreground">{{ $merchant->brand_name ?? 'N/A' }}</h1>
                        @if($merchant->status == 'active')
                            <i class="ki-filled ki-verify text-primary text-xl"></i>
                        @endif
                    </div>
                    <div class="flex flex-wrap justify-center items-center gap-4 text-sm font-medium">
                        <div class="flex items-center gap-1.5 text-muted-foreground">
                            <i class="ki-filled ki-profile-circle text-base"></i>
                            {{ $merchant->full_name }}
                        </div>
                        <div class="flex items-center gap-1.5 text-muted-foreground">
                            <i class="ki-filled ki-sms text-base"></i>
                            <a class="hover:text-primary transition-colors" href="mailto:{{ $merchant->email }}">
                                {{ $merchant->email }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Team Section (Optional/Placeholder) -->
                <div class="grid justify-center gap-2 mb-8">
                    <span class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest text-center">
                        OPERATING REGION
                    </span>
                    <div class="flex items-center justify-center gap-2 px-3 py-1 bg-muted/50 rounded-full border border-border">
                         <i class="ki-filled ki-geolocation text-xs text-primary"></i>
                         <span class="text-xs font-bold text-foreground">{{ $merchant->brand_country ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Stats Dashed Cards -->
                <div class="flex items-center justify-center flex-wrap gap-3 lg:gap-6">
                    <div class="grid grid-cols-1 gap-1 border border-dashed border-border rounded-xl px-5 py-3 min-w-32 bg-muted/10">
                        <span class="text-mono text-xl font-bold text-foreground">0</span>
                        <span class="text-muted-foreground text-xs font-semibold uppercase tracking-tight">Transactions</span>
                    </div>
                    <div class="grid grid-cols-1 gap-1 border border-dashed border-border rounded-xl px-5 py-3 min-w-32 bg-muted/10">
                        <span class="text-mono text-xl font-bold text-foreground">0</span>
                        <span class="text-muted-foreground text-xs font-semibold uppercase tracking-tight">Customers</span>
                    </div>
                    <div class="grid grid-cols-1 gap-1 border border-dashed border-border rounded-xl px-5 py-3 min-w-32 bg-muted/10">
                        <span class="text-mono text-xl font-bold text-foreground">$0.00</span>
                        <span class="text-muted-foreground text-xs font-semibold uppercase tracking-tight">Volume</span>
                    </div>
                    <div class="grid grid-cols-1 gap-1 border border-dashed border-{{ $merchant->status == 'active' ? 'primary' : 'danger' }} rounded-xl px-5 py-3 min-w-32 bg-{{ $merchant->status == 'active' ? 'primary' : 'danger' }}/5">
                        <span class="text-mono text-xl font-bold text-{{ $merchant->status == 'active' ? 'primary' : 'danger' }}">{{ strtoupper($merchant->status) }}</span>
                        <span class="text-{{ $merchant->status == 'active' ? 'primary' : 'danger' }}/60 text-xs font-semibold uppercase tracking-tight">Account Status</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
            <!-- Left Column: Highlights -->
            <div class="flex flex-col gap-5 lg:gap-7.5">
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Account Highlights</h3>
                    </div>
                    <div class="kt-card-content pt-4 pb-6">
                        <div class="flex flex-col gap-5 px-5 lg:px-7.5">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-muted-foreground">Merchant ID:</span>
                                <span class="text-sm font-bold text-foreground text-mono">{{ $merchant->a_id }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-muted-foreground">Username:</span>
                                <span class="text-sm font-bold text-foreground">{{ $merchant->username }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-muted-foreground">Joined Date:</span>
                                <span class="text-sm font-bold text-foreground">{{ \Carbon\Carbon::parse($merchant->created_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-muted-foreground">Country:</span>
                                <span class="text-sm font-bold text-foreground">{{ $merchant->brand_country ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-muted-foreground">Branding ID:</span>
                                <span class="text-sm font-bold text-foreground text-mono text-xs">{{ $merchant->brand_id }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Support Channels</h3>
                    </div>
                    <div class="kt-card-content pt-4 pb-6 px-5 lg:px-7.5">
                        <div class="grid gap-4">
                            <div class="flex items-center gap-3">
                                <div class="size-9 rounded-lg bg-accent/60 flex items-center justify-center border border-border">
                                    <i class="ki-filled ki-sms text-lg text-primary"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-semibold text-muted-foreground uppercase">Email</span>
                                    <a href="mailto:{{ $merchant->support_email }}" class="text-sm font-medium hover:text-primary">{{ $merchant->support_email ?? 'N/A' }}</a>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="size-9 rounded-lg bg-accent/60 flex items-center justify-center border border-border">
                                    <i class="ki-filled ki-phone text-lg text-primary"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-semibold text-muted-foreground uppercase">Phone</span>
                                    <span class="text-sm font-medium">{{ $merchant->support_phone ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="size-9 rounded-lg bg-accent/60 flex items-center justify-center border border-border">
                                    <i class="ki-filled ki-global text-lg text-primary"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-semibold text-muted-foreground uppercase">Website</span>
                                    <a href="{{ $merchant->support_website }}" target="_blank" class="text-sm font-medium hover:text-primary">{{ $merchant->support_website ?? 'N/A' }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Brand Profile -->
            <div class="lg:col-span-2 flex flex-col gap-5 lg:gap-7.5">
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Brand Profile</h3>
                    </div>
                    <div class="kt-card-content pt-4 pb-7 px-5 lg:px-7.5">
                        <div class="flex flex-col gap-5">
                            <div class="flex flex-col gap-2">
                                <h4 class="text-base font-bold text-foreground">About the Brand</h4>
                                <p class="text-sm text-secondary-foreground leading-relaxed">
                                    {{ $merchant->brand_details ?? 'No additional brand details provided. This merchant operates as an administrative entity within the platform.' }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                                <div class="p-5 rounded-xl border border-dashed border-border bg-muted/30">
                                    <div class="flex items-center gap-3 mb-3">
                                        <i class="ki-filled ki-setting-4 text-xl text-primary"></i>
                                        <span class="font-bold">General Settings</span>
                                    </div>
                                    <div class="grid gap-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-muted-foreground">Currency:</span>
                                            <span class="font-medium">BDT</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-muted-foreground">Timezone:</span>
                                            <span class="font-medium">Asia/Dhaka</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-muted-foreground">Language:</span>
                                            <span class="font-medium">English</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-5 rounded-xl border border-dashed border-border bg-muted/30">
                                    <div class="flex items-center gap-3 mb-3">
                                        <i class="ki-filled ki-shield-search text-xl text-primary"></i>
                                        <span class="font-bold">Compliance</span>
                                    </div>
                                    <div class="grid gap-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-muted-foreground">KYC Status:</span>
                                            <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-warning">Pending</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-muted-foreground">Risk Level:</span>
                                            <span class="kt-badge kt-badge-sm kt-badge-outline kt-badge-success">Low</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-muted-foreground">Verification:</span>
                                            <span class="font-medium">Self-Certified</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Placeholder -->
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Recent Activity</h3>
                    </div>
                    <div class="kt-card-content py-10 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i class="ki-filled ki-calendar-tick text-4xl text-muted-foreground/30"></i>
                            <span class="text-muted-foreground font-medium">No recent activity found for this merchant.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('superadmin.partials._modals')
@endsection
