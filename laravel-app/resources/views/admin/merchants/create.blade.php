@php
    $permissions = json_decode($global_response_permission['response'][0]['permission'] ?? '[]', true);
    $userRole = $global_user_response['response'][0]['role'] ?? 'staff';
    $isSuper = ($userRole === 'root' || $userRole === 'admin');
@endphp

<div class="page-header d-print-none" aria-label="Page header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">
                    <ol class="breadcrumb breadcrumb-arrow mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0)"
                                onclick="load_content('Merchants','{{ $site_url }}{{ $path_admin }}/merchants','nav-item-merchants')">Merchants</a>
                        </li>
                        <li class="breadcrumb-item active"><a href="javascript:void(0)">Create Merchant</a></li>
                    </ol>
                </div>
                <h2 class="page-title">Create New Merchant</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list align-items-center gap-3">
                    <span class="global-loaderSpinner"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <form class="form-submit" enctype="multipart/form-data">
            <input type="hidden" name="action" value="merchant-create">
            <input type="hidden" name="csrf_token" value="{{ $csrfToken }}">

            <div class="row row-deck row-cards">
                {{-- Tabs Navigation --}}
                <div class="col-12 mb-2 d-flex justify-content-center">
                    <div>
                        <div class="card p-2">
                            <ul class="nav nav-pills gap-2" role="tablist" id="merchantTabs"
                                style="font-weight: 500; font-size: .875rem;">
                                <li class="nav-item">
                                    <div class="nav-link active" style="cursor: pointer" data-type="general">
                                        General
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <div class="nav-link" style="cursor: pointer" data-type="business_details">
                                        Business Details
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <div class="nav-link" style="cursor: pointer" data-type="logo_favicon">
                                        Logo & Favicon
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <div class="nav-link" style="cursor: pointer" data-type="contact_social">
                                        Contact & Social
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- General Tab --}}
                <div class="col-12 tab-content-item tab-general">
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Personal Information</h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label required">Full Name</label>
                                    <input type="text" class="form-control" name="full_name"
                                        placeholder="Enter merchant full name" required>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label required">Username</label>
                                    <input type="text" class="form-control" name="username"
                                        placeholder="Enter unique username" required>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label required">Email Address</label>
                                    <input type="email" class="form-control" name="email"
                                        placeholder="Enter email address" required>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label required">Password</label>
                                    <input type="password" class="form-control" name="password"
                                        placeholder="Enter login password" required>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label required">Subscription Plan</label>
                                    <select class="form-select js-select" name="plan_id" data-search="true" required>
                                        <option value="">Select Plan</option>
                                        @foreach ($plans as $plan)
                                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label required">Account Status</label>
                                    <select class="form-select js-select" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="suspend">Suspend</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Business Details Tab --}}
                <div class="col-12 tab-content-item tab-business_details" style="display: none">
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Business Information</h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label required">Site/Brand Name</label>
                                    <input type="text" class="form-control" name="brand_name"
                                        placeholder="Enter business name" required>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label required">Default Timezone</label>
                                    <select class="form-select js-select" name="default_timezone" data-search="true" required>
                                        @foreach (DateTimeZone::listIdentifiers() as $tz)
                                            <option value="{{ $tz }}" {{ $tz === 'Asia/Dhaka' ? 'selected' : '' }}>{{ $tz }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label required">Default Language</label>
                                    <select class="form-select js-select" name="default_language" required>
                                        <option value="en" selected>English</option>
                                        <option value="bn">Bangla</option>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label required">Default Currency</label>
                                    <select class="form-select js-select" name="default_currency" required>
                                        <option value="BDT" selected>BDT</option>
                                        <option value="USD">USD</option>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">Street Address</label>
                                    <input type="text" class="form-control" name="street_address"
                                        placeholder="Enter address">
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">City/Town</label>
                                    <input type="text" class="form-control" name="city_town"
                                        placeholder="Enter city">
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" name="postal_code"
                                        placeholder="Enter postal code">
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">Country</label>
                                    <input type="text" class="form-control" name="country"
                                        placeholder="Enter country">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Logo & Favicon Tab --}}
                <div class="col-12 tab-content-item tab-logo_favicon" style="display: none">
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Brand Assets</h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label">Favicon</label>
                                    <input type="file" class="form-control img-input" name="favicon" data-preview="preview-favicon">
                                    <div class="border rounded p-2 mt-2 d-flex align-items-center justify-content-center" style="height: 90px; width: 90px;">
                                        <img src="" id="preview-favicon" style="max-width: 100%; max-height: 100%; display: none;">
                                        <div class="text-muted small preview-placeholder">No image</div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">Primary Logo</label>
                                    <input type="file" class="form-control img-input" name="primary_logo" data-preview="preview-logo">
                                    <div class="border rounded p-2 mt-2 d-flex align-items-center justify-content-center" style="height: 90px; max-width: 300px;">
                                        <img src="" id="preview-logo" style="max-width: 100%; max-height: 100%; display: none;">
                                        <div class="text-muted small preview-placeholder">No image</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact & Social Tab --}}
                <div class="col-12 tab-content-item tab-contact_social" style="display: none">
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Support Information</h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label">Support Email</label>
                                    <input type="email" class="form-control" name="support_email_address" placeholder="support@domain.com">
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">Support Phone</label>
                                    <input type="text" class="form-control" name="support_phone_number" placeholder="+123456789">
                                </div>
                                <div class="col-lg-12">
                                    <label class="form-label">Website URL</label>
                                    <input type="url" class="form-control" name="support_website" placeholder="https://domain.com">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Social Profiles</h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label">WhatsApp Number</label>
                                    <input type="text" class="form-control" name="whatsapp_number" placeholder="+123456789">
                                </div>
                                <div class="col-lg-6">
                                    <label class="form-label">Telegram Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text">t.me/</span>
                                        <input type="text" class="form-control" name="telegram" placeholder="username">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="col-12 mt-3">
                    <div class="d-flex align-items-center">
                        <a href="javascript:void(0)"
                            onclick="load_content('Merchants','{{ $site_url }}{{ $path_admin }}/merchants','nav-item-merchants')"
                            class="btn btn-link link-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-saveChanges ms-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg>
                            Create Merchant
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script data-cfasync="false">
    {{-- Tab Logic --}}
    document.querySelectorAll('#merchantTabs .nav-link').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#merchantTabs .nav-link').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const type = this.dataset.type;
            document.querySelectorAll('.tab-content-item').forEach(el => el.style.display = 'none');
            document.querySelector('.tab-' + type).style.display = 'block';
        });
    });

    {{-- Image Preview Logic --}}
    document.querySelectorAll('.img-input').forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            const previewId = this.dataset.preview;
            const preview = document.getElementById(previewId);
            const placeholder = this.parentElement.querySelector('.preview-placeholder');

            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    });

    {{-- Form Submission --}}
    $('.form-submit').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        var btnClass = 'btn-saveChanges';
        var btnOrig = document.querySelector('.' + btnClass).innerHTML;
        var loaderSpinner = 'global-loaderSpinner';

        document.querySelector('.' + btnClass).innerHTML =
            '<div class="spinner-border spinner-border-sm" role="status"></div>';
        document.querySelector('.' + loaderSpinner).innerHTML =
            '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>';

        $.ajax({
            type: 'POST',
            url: '{{ $site_url }}{{ $path_admin }}/dashboard',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                document.querySelector('.' + btnClass).innerHTML = btnOrig;
                document.querySelector('.' + loaderSpinner).innerHTML = '';
                updateCsrfTokens(response.csrf_token);

                if (response.status === 'true') {
                    createToast({
                        title: response.title,
                        description: response.message,
                        svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#5f38f9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-circle-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>`,
                        timeout: 6000,
                        top: 70
                    });
                    load_content('Merchants', '{{ $site_url }}{{ $path_admin }}/merchants',
                        'nav-item-merchants');
                } else {
                    createToast({
                        title: response.title,
                        description: response.message,
                        svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`,
                        timeout: 6000,
                        top: 70
                    });
                }
            },
            error: function() {
                document.querySelector('.' + btnClass).innerHTML = btnOrig;
                document.querySelector('.' + loaderSpinner).innerHTML = '';
            }
        });
    });

    function updateCsrfTokens(token) {
        if (!token) return;
        $('input[name="csrf_token"]').val(token);
        $('input[name="csrf_token_default"]').val(token);
    }
</script>
