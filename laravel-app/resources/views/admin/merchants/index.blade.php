@php
    $permissions = json_decode($global_response_permission['response'][0]['permission'] ?? '[]', true);
    $userRole = $global_user_response['response'][0]['role'] ?? 'staff';
    $isSuper = ($userRole === 'root' || $userRole === 'admin');

    $canCreate = $isSuper || ($permissions['merchants']['create'] ?? false);
    $canEdit = $isSuper || ($permissions['merchants']['edit'] ?? false);
    $canDelete = $isSuper || ($permissions['merchants']['delete'] ?? false);
@endphp

<style>
    .table-responsive table thead tr {
        height: 46px;
    }

    .table-responsive table tbody tr {
        height: 66px;
    }
</style>

<div class="page-header d-print-none" aria-label="Page header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Management</div>
                <h2 class="page-title">Merchants</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list align-items-center gap-3">
                    <span class="global-loaderSpinner"></span>
                    @if ($canCreate)
                        <span
                            onclick="load_content('Create Merchant','{{ $site_url }}{{ $path_admin }}/merchants/create','nav-item-merchants')">
                            <a href="javascript:void(0)" class="btn btn-primary btn-5 d-none d-sm-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 5l0 14" />
                                    <path d="M5 12l14 0" />
                                </svg>
                                Create Merchant
                            </a>
                            <a href="javascript:void(0)" class="btn btn-primary btn-6 d-sm-none btn-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 5l0 14" />
                                    <path d="M5 12l14 0" />
                                </svg>
                            </a>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-deck row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="w-100" style="border-bottom: 1px solid #e8e7ec;">
                        <div class="filter-tab-data p-4 d-none">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Filters</h3>
                                <h5 class="text-danger" style=" font-size: 14px; cursor: pointer; "
                                    onclick="filter_hide_show_reset('filter-tab-data')">Reset</h5>
                            </div>

                            <div class="row g-3" style=" margin-top: -10px; margin-bottom: -25px; ">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="filter-status" class="form-label">Status</label>
                                        <div class="form-control-wrap">
                                            <select class="form-select" id="filter-status">
                                                <option value="">All Status</option>
                                                <option value="active">Active</option>
                                                <option value="suspend">Suspend</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="filter-created-from" class="form-label">Created From</label>
                                        <div class="form-control-wrap">
                                            <input placeholder="dd/mm/yyyy" type="date" class="form-control"
                                                id="filter-created-from">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="filter-created-until" class="form-label">Created Until</label>
                                        <div class="form-control-wrap">
                                            <input placeholder="dd/mm/yyyy" type="date" class="form-control"
                                                id="filter-created-until">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            style="display: flex; flex-direction: row-reverse; height: 53px; align-items: center; padding-right: 20px; font-size: 22px;">
                            <svg onclick="filter_hide_show('filter-tab-data')" style="cursor: pointer;"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-filter">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-6 2v-8.5l-4.48 -4.928a2 2 0 0 1 -.52 -1.345v-2.227z" />
                            </svg>
                        </div>
                    </div>

                    <div class="card-body border-bottom py-3">
                        <div class="row g-4">
                            <div class="col-lg-6 col-md-6">
                                <div class="text-secondary">
                                    Show
                                    <div class="mx-2 d-inline-block">
                                        <input type="text" class="form-control form-control-sm show_limit" value="10"
                                            size="3" aria-label="count">
                                    </div>
                                    entries
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 d-flex align-items-center justify-content-right gap-2">
                                <div class="ms-auto text-secondary">
                                    Search:
                                    <div class="ms-2 d-inline-block">
                                        <input type="text" class="form-control form-control-sm search_input"
                                            aria-label="Search">
                                    </div>
                                </div>

                                <button class="btn btn-danger bulk-action d-none" data-bs-toggle="modal"
                                    data-bs-target="#model-bulkAction">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-square">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M3 5a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-14" />
                                    </svg>
                                    <span id="bulkActionBTN-count">(0)</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-selectable card-table table-vcenter text-nowrap datatable">
                            <thead>
                                <tr>
                                    <th class="w-1"><input class="form-check-input m-0 align-middle select-all"
                                            type="checkbox" aria-label="Select all merchants"></th>
                                    <th>Merchant</th>
                                    <th>Username</th>
                                    <th>Brand</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="table-data-list">
                                {{-- Data loaded via AJAX --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        <div class="row g-2 justify-content-center justify-content-sm-between">
                            <div class="col-auto d-flex align-items-center">
                                <p class="m-0 text-secondary table-data-list-entries"></p>
                            </div>
                            <div class="col-auto table-data-list-pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Action Modal --}}
<div class="modal fade" id="model-bulkAction" data-bs-keyboard="false" tabindex="-1" aria-labelledby="scrollableLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-top">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title model-bulkAction-title" id="scrollableLabel">Action for Selected Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mt-1">
                    <label for="model-bulkActionID" class="form-label">Action <span class="text-danger">*</span></label>
                    <div class="form-control-wrap">
                        <select class="form-select" id="model-bulkActionID">
                            <option value="" selected>Select an Action</option>
                            <option value="suspend">Suspend Selected</option>
                            <option value="unsuspend">Activate Selected</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary model-bulkAction-btn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script data-cfasync="false">
    var currentPage = 1;

    function load_data_list(page = 1) {
        currentPage = page;
        var csrf_token_default = $('input[name="csrf_token_default"]').val();
        var search_input = $('.search_input').val();
        var show_limit = $('.show_limit').val();
        var filter_status = $('#filter-status').val();
        var filter_start = $('#filter-created-from').val();
        var filter_end = $('#filter-created-until').val();

        $(".table-data-list").html(
            '<tr><td colspan="7" class="text-center text-muted"><div class="spinner-border text-primary" style="margin: 50px;">  <span class="visually-hidden">Loading...</span></div></td></tr>'
        );

        $.ajax({
            type: 'POST',
            url: '{{ route('admin.merchants.list.ajax') }}',
            data: {
                _token: '{{ csrf_token() }}',
                search_input: search_input,
                show_limit: show_limit,
                page: page,
                filter_status: filter_status,
                filter_start: filter_start,
                filter_end: filter_end
            },
            dataType: 'json',
            success: function(res) {
                let html = '';
                updateCsrfTokens(res.csrf_token);

                if (res.status === 'true') {
                    res.response.forEach(item => {
                        let badge = 'secondary';
                        if (item.status === 'active') badge = 'primary';
                        if (item.status === 'suspend') badge = 'danger';

                        let redirectEdit = '';
                        // For now we don't have edit page implemented yet
                        // redirectEdit = `style="cursor:pointer;" onclick="load_content('Edit Merchant','{{ $site_url }}{{ $path_admin }}/merchants/edit?ref=${item.id}','nav-item-merchants')"`;

                        html += `
                            <tr data-id="${item.id}">
                                <td><input class="form-check-input m-0 align-middle table-selectable-check rowCheckbox" type="checkbox"></td>
                                <td ${redirectEdit}>
                                    <div class="d-flex py-1 align-items-center">
                                        <div class="flex-fill">
                                            <div class="font-weight-medium">${item.full_name}</div>
                                            <div class="text-secondary">${item.email}</div>
                                        </div>
                                    </div>
                                </td>
                                <td ${redirectEdit}>${item.username}</td>
                                <td ${redirectEdit}>
                                    <div class="font-weight-medium">${item.brand_name}</div>
                                    <div class="text-secondary small">${item.brand_id}</div>
                                </td>
                                <td ${redirectEdit}><span class="badge bg-${badge} me-1"></span> ${item.status.charAt(0).toUpperCase() + item.status.slice(1)}</td>
                                <td ${redirectEdit}>${item.created_date}</td>
                                <td class="text-end">
                                    <span class="dropdown" style="position: unset;">
                                        <button class="btn dropdown-toggle align-text-top" data-bs-boundary="viewport" data-bs-toggle="dropdown">Actions</button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="merchantBulkAction('${item.status === 'active' ? 'suspend' : 'unsuspend'}', ['${item.id}'])">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-${item.status === 'active' ? 'lock' : 'lock-open'} me-2">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M5 11m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z" />
                                                    <path d="M12 16m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                    <path d="M8 11v-4a4 4 0 1 1 8 0v4" />
                                                </svg>
                                                ${item.status === 'active' ? 'Suspend' : 'Unsuspend'}
                                            </a>
                                        </div>
                                    </span>
                                </td>
                            </tr>
                        `;
                    });
                    $(".table-data-list").html(html);
                    initCheckboxTable();
                    $(".table-data-list-entries").html(res.datatableInfo);
                    $(".table-data-list-pagination").html(res.pagination);
                } else {
                    html = `<td colspan="7" class="text-center text-muted"> 
                                <div style="margin: 80px 0;"> 
                                    <center> 
                                        <svg xmlns="http://www.w3.org/2000/svg" style=" width: 48px; height: 48px; color: #667085; " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-mood-cry">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M9 10l.01 0" />
                                            <path d="M15 10l.01 0" />
                                            <path d="M9.5 15.25a3.5 3.5 0 0 1 5 0" />
                                            <path d="M17.566 17.606a2 2 0 1 0 2.897 .03l-1.463 -1.636l-1.434 1.606z" />
                                            <path d="M20.865 13.517a8.937 8.937 0 0 0 .135 -1.517a9 9 0 1 0 -9 9c.69 0 1.36 -.076 2 -.222" />
                                        </svg> 
                                        <h3 style=" font-weight: 600; font-size: 18px; margin-top: 15px; margin-bottom: 5px; color: #1d2939;">${res.title || 'Nothing Here Yet'}</h3> 
                                        <p style=" margin: 0; font-size: 14px; color: #667085;">${res.message || 'No data is available at the moment.'}</p> 
                                    </center> 
                                </div> 
                            </td>`;
                    $(".table-data-list").html(html);
                    $(".table-data-list-entries").html('Showing <strong>0 to 0</strong> of <strong>0 entries</strong>');
                    $(".table-data-list-pagination").html('');
                }
            }
        });
    }

    function initCheckboxTable() {
        const selectAll = document.querySelector('.select-all');
        const rowCheckboxes = document.querySelectorAll('.rowCheckbox');
        const bulkActionBTN = document.querySelector('.bulk-action');

        if (!selectAll) return;

        function updateSelection() {
            const selected = document.querySelectorAll('.rowCheckbox:checked');
            document.getElementById("bulkActionBTN-count").innerHTML = `(${selected.length})`;
            if (selected.length > 0) {
                bulkActionBTN.classList.remove('d-none');
            } else {
                bulkActionBTN.classList.add('d-none');
            }
        }

        selectAll.addEventListener('change', () => {
            rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            updateSelection();
        });

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                selectAll.checked = rowCheckboxes.length === document.querySelectorAll('.rowCheckbox:checked').length;
                updateSelection();
            });
        });
    }

    $('.model-bulkAction-btn').click(function() {
        var actionID = document.querySelector("#model-bulkActionID").value;
        const selectedRows = Array.from(document.querySelectorAll('.rowCheckbox:checked')).map(cb => cb.closest('tr')
            .dataset.id);

        if (actionID == "") {
            createToast({
                title: 'Action Required',
                description: 'Please select an action to proceed.',
                svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`,
                timeout: 6000,
                top: 70
            });
            return;
        }

        merchantBulkAction(actionID, selectedRows);
        $('#model-bulkAction').modal('hide');
    });

    function merchantBulkAction(actionId, selectedIds) {
        if (!selectedIds.length) return;

        var loaderSpinner = 'global-loaderSpinner';
        document.querySelector('.' + loaderSpinner).innerHTML =
            '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>';

        $.ajax({
            type: 'POST',
            url: '{{ $site_url }}{{ $path_admin }}/dashboard',
            data: {
                action: 'merchant-bulk-action',
                action_id: actionId,
                selected_ids: selectedIds,
                csrf_token: $('input[name="csrf_token_default"]').val()
            },
            dataType: 'json',
            success: function(response) {
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
                    load_data_list(currentPage);
                    document.querySelector('.bulk-action').classList.add('d-none');
                } else {
                    createToast({
                        title: response.title,
                        description: response.message,
                        svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`,
                        timeout: 6000,
                        top: 70
                    });
                }
            }
        });
    }

    function updateCsrfTokens(token) {
        if (!token) return;
        $('input[name="csrf_token"]').val(token);
        $('input[name="csrf_token_default"]').val(token);
    }

    function filter_hide_show_reset(className) {
        $('#filter-status').val('');
        $('#filter-created-from').val('');
        $('#filter-created-until').val('');
        load_data_list(1);
    }

    $(document).on('click', '.table-data-list-pagination button', function() {
        load_data_list($(this).data('page'));
    });

    $('.search_input, .show_limit, #filter-status, #filter-created-from, #filter-created-until').on('change',
    function() {
        load_data_list(1);
    });

    load_data_list(1);
</script>
