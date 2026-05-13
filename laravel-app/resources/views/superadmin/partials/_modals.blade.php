<!-- Suspend Modal -->
<div class="kt-modal" data-kt-modal="true" id="suspend_merchant_modal">
    <div class="kt-modal-content max-w-[500px] top-[15%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Suspend Merchant</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body">
            <div class="p-5 text-center">
                <i class="ki-filled ki-information-2 text-5xl text-warning mb-5"></i>
                <p class="text-lg font-medium mb-2">Are you sure you want to suspend this merchant?</p>
                <p class="text-secondary-foreground text-sm mb-5">
                    The merchant <span id="suspend_merchant_name" class="font-bold"></span> will lose access to their dashboard until reactivated.
                </p>
                <form id="suspend_merchant_form" method="POST">
                    @csrf
                    <div class="flex justify-center gap-2.5 mt-5">
                        <button type="submit" class="kt-btn kt-btn-danger">Yes, Suspend</button>
                        <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reactivate Modal -->
<div class="kt-modal" data-kt-modal="true" id="reactivate_merchant_modal">
    <div class="kt-modal-content max-w-[500px] top-[15%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Reactivate Merchant</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body">
            <div class="p-5 text-center">
                <i class="ki-filled ki-check-circle text-5xl text-success mb-5"></i>
                <p class="text-lg font-medium mb-2">Are you sure you want to reactivate this merchant?</p>
                <p class="text-secondary-foreground text-sm mb-5">
                    The merchant <span id="reactivate_merchant_name" class="font-bold"></span> will regain access to their dashboard.
                </p>
                <form id="reactivate_merchant_form" method="POST">
                    @csrf
                    <div class="flex justify-center gap-2.5 mt-5">
                        <button type="submit" class="kt-btn kt-btn-success">Yes, Reactivate</button>
                        <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Merchant Details Modal -->
<div class="kt-modal" data-kt-modal="true" id="view_merchant_modal">
    <div class="kt-modal-content max-w-[700px] top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Merchant Details</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost shrink-0" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <div class="kt-modal-body p-0">
            <div class="p-7">
                <div class="flex items-center gap-5 mb-7">
                    <img id="view_merchant_logo" src="" class="size-20 rounded-lg object-cover border border-border" alt="Logo">
                    <div class="flex flex-col gap-1">
                        <h4 id="view_merchant_brand_name" class="text-xl font-bold text-mono"></h4>
                        <span id="view_merchant_full_name" class="text-base text-secondary-foreground"></span>
                        <div id="view_merchant_status_badge"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase text-muted-foreground">Admin Email</span>
                        <span id="view_merchant_email" class="text-sm font-medium"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase text-muted-foreground">Username</span>
                        <span id="view_merchant_username" class="text-sm font-medium"></span>
                    </div>
                    <div class="flex flex-col gap-1 border-t border-border pt-3">
                        <span class="text-xs font-semibold uppercase text-muted-foreground">Merchant ID</span>
                        <span id="view_merchant_id" class="text-sm font-medium"></span>
                    </div>
                    <div class="flex flex-col gap-1 border-t border-border pt-3">
                        <span class="text-xs font-semibold uppercase text-muted-foreground">Country</span>
                        <span id="view_merchant_country" class="text-sm font-medium"></span>
                    </div>
                    <div class="flex flex-col gap-1 border-t border-border pt-3">
                        <span class="text-xs font-semibold uppercase text-muted-foreground">Joined Date</span>
                        <span id="view_merchant_joined" class="text-sm font-medium"></span>
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer justify-end p-5 border-t border-border">
                <button class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openSuspendModal(id, name) {
        document.getElementById('suspend_merchant_name').innerText = name;
        document.getElementById('suspend_merchant_form').action = "{{ route('superadmin.merchants.index') }}/" + id + "/suspend";
        KTModal.getInstance(document.getElementById('suspend_merchant_modal')).show();
    }

    function openReactivateModal(id, name) {
        document.getElementById('reactivate_merchant_name').innerText = name;
        document.getElementById('reactivate_merchant_form').action = "{{ route('superadmin.merchants.index') }}/" + id + "/reactivate";
        KTModal.getInstance(document.getElementById('reactivate_merchant_modal')).show();
    }

    function openViewModal(data) {
        document.getElementById('view_merchant_brand_name').innerText = data.brand_name || 'N/A';
        document.getElementById('view_merchant_full_name').innerText = data.full_name || 'N/A';
        document.getElementById('view_merchant_email').innerText = data.email || 'N/A';
        document.getElementById('view_merchant_username').innerText = data.username || 'N/A';
        document.getElementById('view_merchant_id').innerText = data.a_id || 'N/A';
        document.getElementById('view_merchant_country').innerText = data.brand_country || 'N/A';
        document.getElementById('view_merchant_joined').innerText = data.created_date || 'N/A';
        
        const logo = data.brand_logo || "{{ asset('assets/media/avatars/blank.png') }}";
        document.getElementById('view_merchant_logo').src = logo;

        const statusBadge = document.getElementById('view_merchant_status_badge');
        if (data.status === 'active') {
            statusBadge.innerHTML = '<span class="kt-badge kt-badge-outline kt-badge-success">Active</span>';
        } else {
            statusBadge.innerHTML = '<span class="kt-badge kt-badge-outline kt-badge-danger">Suspended</span>';
        }

        KTModal.getInstance(document.getElementById('view_merchant_modal')).show();
    }
</script>
