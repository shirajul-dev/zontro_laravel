@extends('superadmin.layouts.app')

@section('title', 'All Merchants')

@section('content')
<!-- Container -->
<div class="kt-container-fixed">
    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
        <div class="flex flex-col justify-center gap-2">
            <h1 class="text-xl font-medium leading-none text-mono">
                Merchants
            </h1>
            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                Manage your platform merchants ({{ count($merchants) }})
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <a class="kt-btn kt-btn-primary" href="{{ route('superadmin.merchants.create') }}">
                <i class="ki-filled ki-plus"></i>
                Add Merchant
            </a>
        </div>
    </div>
</div>
<!-- End of Container -->

<!-- Container -->
<div class="kt-container-fixed">
    @if(session('success'))
        <div class="kt-alert kt-alert-outline kt-alert-success mb-5">
            <i class="ki-filled ki-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="kt-alert kt-alert-outline kt-alert-danger mb-5">
            <i class="ki-filled ki-information-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="kt-card">
        <div class="kt-card-header">
            <h3 class="kt-card-title">All Merchants</h3>
            <div class="kt-card-toolbar">
                <div class="kt-input-icon kt-input-icon-start max-w-[200px]">
                    <i class="ki-filled ki-magnifier"></i>
                    <input class="kt-input kt-input-sm" id="merchant_search" placeholder="Search..." type="text">
                </div>
            </div>
        </div>
        <div class="kt-card-content">
            <div class="kt-scrollable-x-auto">
                <table class="kt-table table-auto kt-table-border" id="merchants_table">
                    <thead>
                        <tr>
                            <th class="min-w-[250px]">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label text-xs uppercase font-semibold">Merchant / Brand</span>
                                </span>
                            </th>
                            <th class="min-w-[150px]">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label text-xs uppercase font-semibold">Email</span>
                                </span>
                            </th>
                            <th class="min-w-[120px]">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label text-xs uppercase font-semibold">Country</span>
                                </span>
                            </th>
                            <th class="min-w-[120px]">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label text-xs uppercase font-semibold">Status</span>
                                </span>
                            </th>
                            <th class="min-w-[150px]">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label text-xs uppercase font-semibold">Joined Date</span>
                                </span>
                            </th>
                            <th class="w-[100px] text-center">
                                <span class="kt-table-col">
                                    <span class="kt-table-col-label text-xs uppercase font-semibold">Actions</span>
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($merchants as $merchant)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $merchant->brand_logo ?: asset('assets/media/avatars/blank.png') }}" class="rounded-lg size-10 object-cover border border-border" alt="Logo">
                                    <div class="flex flex-col">
                                        <a href="#" onclick='openViewModal(@json($merchant))' class="text-sm font-semibold text-mono hover:text-primary mb-px">
                                            {{ $merchant->brand_name }}
                                        </a>
                                        <span class="text-xs text-secondary-foreground font-normal">
                                            {{ $merchant->full_name }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm text-foreground font-medium">
                                    {{ $merchant->email }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm text-foreground">
                                        {{ $merchant->brand_country ?: 'N/A' }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($merchant->status === 'active')
                                    <span class="kt-badge kt-badge-outline kt-badge-success">Active</span>
                                @else
                                    <span class="kt-badge kt-badge-outline kt-badge-danger">Suspended</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-sm text-secondary-foreground">
                                    {{ \Carbon\Carbon::parse($merchant->created_date)->format('d M, Y') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="kt-menu" data-kt-menu="true">
                                    <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
                                        <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
                                            <i class="ki-filled ki-dots-vertical text-lg"></i>
                                        </button>
                                        <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
                                            <div class="kt-menu-item">
                                                <a class="kt-menu-link" href="{{ route('superadmin.merchants.show', $merchant->a_id) }}">
                                                    <span class="kt-menu-icon"><i class="ki-filled ki-eye"></i></span>
                                                    <span class="kt-menu-title">View Details</span>
                                                </a>
                                            </div>
                                            <div class="kt-menu-item">
                                                <a class="kt-menu-link" href="{{ route('superadmin.merchants.edit', $merchant->a_id) }}">
                                                    <span class="kt-menu-icon"><i class="ki-filled ki-pencil"></i></span>
                                                    <span class="kt-menu-title">Edit Merchant</span>
                                                </a>
                                            </div>
                                            <div class="kt-menu-separator"></div>
                                            <div class="kt-menu-item">
                                                @if($merchant->status === 'active')
                                                    <a class="kt-menu-link text-danger" href="#" onclick="openSuspendModal('{{ $merchant->a_id }}', '{{ $merchant->brand_name }}')">
                                                        <span class="kt-menu-icon text-danger"><i class="ki-filled ki-lock"></i></span>
                                                        <span class="kt-menu-title">Suspend</span>
                                                    </a>
                                                @else
                                                    <a class="kt-menu-link text-success" href="#" onclick="openReactivateModal('{{ $merchant->a_id }}', '{{ $merchant->brand_name }}')">
                                                        <span class="kt-menu-icon text-success"><i class="ki-filled ki-lock-open"></i></span>
                                                        <span class="kt-menu-title">Reactivate</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-10">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="ki-filled ki-profile-circle text-5xl text-muted/20"></i>
                                    <p class="text-muted-foreground">No merchants found.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- End of Container -->

@include('superadmin.partials._modals')

@endsection

@push('scripts')
<script>
    // Search functionality
    document.getElementById('merchant_search').addEventListener('keyup', function() {
        const value = this.value.toLowerCase();
        const rows = document.querySelectorAll('#merchants_table tbody tr');
        
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });
</script>
@endpush
