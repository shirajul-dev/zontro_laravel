@php
    $permissions = json_decode($global_response_permission['response'][0]['permission'] ?? '[]', true);
    $userRole = $global_user_response['response'][0]['role'] ?? 'staff';

    $isSuper = ($userRole === 'root' || $userRole === 'admin');
    $canCreate = $isSuper || ($permissions['brands']['create'] ?? false);
    $canEdit = $isSuper || ($permissions['brands']['edit'] ?? false);
    $canDelete = $isSuper || ($permissions['brands']['delete'] ?? false);
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
                <div class="page-pretitle">Brands</div>
                <h2 class="page-title">Brands</h2>
            </div>

            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list align-items-center gap-3">
                    <span class="global-loaderSpinner"></span>

                    @if ($canCreate)
                        <span
                            onclick="load_content('Brands','{{ $site_url }}{{ $path_admin }}/brands/create','nav-item-brands')">
                            <a href="javascript:void(0)" class="btn btn-primary btn-5 d-none d-sm-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 5l0 14" />
                                    <path d="M5 12l14 0" />
                                </svg>
                                Create Brand
                            </a>
                            <a href="javascript:void(0)" class="btn btn-primary btn-6 d-sm-none btn-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
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
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-filter">
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
                                    Show<div class="mx-2 d-inline-block"><input type="text"
                                            class="form-control form-control-sm show_limit" value="8"
                                            size="3" aria-label="count"></div>entries
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 d-flex align-items-center justify-content-right gap-2">
                                <div class="ms-auto text-secondary">
                                    Search:<div class="ms-2 d-inline-block"><input type="text"
                                            class="form-control form-control-sm search_input" aria-label="Search">
                                    </div>
                                </div>

                                <button class="btn btn-danger bulk-action d-none" data-bs-toggle="modal"
                                    data-bs-target="#model-bulkAction"><svg xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-square">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M3 5a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-14" />
                                    </svg> <span id="bulkActionBTN-count">(0)</span></button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-selectable card-table table-vcenter text-nowrap datatable">
                            <thead>
                                <tr>
                                    <th class="w-1"><input class="form-check-input m-0 align-middle select-all"
                                            type="checkbox" aria-label="Select all"></th>
                                    <th>Brand</th>
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
<div class="modal fade" id="model-bulkAction" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="scrollableLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-top">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title model-bulkAction-title" id="scrollableLabel">Action for Selected Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mt-1">
                    <label for="model-bulkActionID" class="form-label">Action <span
                            class="text-danger">*</span></label>
                    <div class="form-control-wrap">
                        <select class="form-select" id="model-bulkActionID">
                            <option value="" selected>Select an Action</option>
                            @if ($canDelete)
                                <option value="deleted">Delete Selected</option>
                            @endif
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
    $('.model-bulkAction-btn').click(function() {
        var my_action_confirmation_btn = document.querySelector("#my-action-confirmation-btn").value;
        var actionID = document.querySelector("#model-bulkActionID").value;
        var csrf_token_default = $('input[name="csrf_token_default"]').val();

        if (actionID == "") {
            createToast({
                title: 'Action Required',
                description: 'You haven’t selected any action. Please choose one to proceed.',
                svg: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`,
                timeout: 6000,
                top: 70
            });
        } else {
            const selectedRows = Array.from(document.querySelectorAll('.rowCheckbox:checked')).map(cb => cb
                .closest('tr').dataset.id);
            var loaderSpinner = 'global-loaderSpinner';

            if (my_action_confirmation_btn !== "") {
                document.querySelector('.' + loaderSpinner).innerHTML =
                    '<div class="spinner-border spinner-border-md text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';

                $.ajax({
                    type: 'POST',
                    url: '{{ $site_url }}{{ $path_admin }}/dashboard',
                    {{-- Keep bulk actions on legacy handler for now or migrate them too --}}
                    data: {
                        action: "brand-bulk-action",
                        csrf_token: csrf_token_default,
                        actionID: actionID,
                        selected_ids: JSON.stringify(selectedRows)
                    },
                    dataType: 'json',
                    success: function(response) {
                        closeAllBootstrapModals();
                        document.querySelector("#my-action-confirmation-btn").value = '';
                        document.getElementById("model-bulkActionID").selectedIndex = 0;
                        document.querySelector('.' + loaderSpinner).innerHTML = '';

                        updateCsrfTokens(response.csrf_token);

                        if (response.status === 'true') {
                            location.reload();
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
                        document.querySelector('.' + loaderSpinner).innerHTML = '';
                    }
                });
            } else {
                show_action_confirmation_tab('model-bulkAction-btn', 'Confirm Action', 'Confirm', 'btn-danger');
            }
        }
    });

    function initCheckboxTable() {
        const selectAll = document.querySelector('.select-all');
        const rowCheckboxes = document.querySelectorAll('.rowCheckbox');
        const bulkActionBTN = document.querySelector('.bulk-action');

        function updateSelection() {
            const selected = document.querySelectorAll('.rowCheckbox:checked');
            document.getElementById("bulkActionBTN-count").innerHTML = `(${selected.length})`;
            if (selected.length > 0) {
                bulkActionBTN.classList.remove('d-none');
            } else {
                bulkActionBTN.classList.add('d-none');
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', () => {
                rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
                updateSelection();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                if (selectAll) selectAll.checked = rowCheckboxes.length === document.querySelectorAll(
                    '.rowCheckbox:checked').length;
                updateSelection();
            });
        });
    }

    function deleteItem(ItemID) {
        var my_action_confirmation_btn = document.querySelector("#my-action-confirmation-btn").value;
        var csrf_token_default = $('input[name="csrf_token_default"]').val();
        var btnClass = 'btnDeleteItem-' + ItemID;

        if (my_action_confirmation_btn !== "") {
            var btn = document.querySelector('#model-my-action-confirmation-btn').innerHTML;
            document.querySelector('#model-my-action-confirmation-btn').innerHTML =
                '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';

            $.ajax({
                type: 'POST',
                url: '{{ $site_url }}{{ $path_admin }}/dashboard',
                data: {
                    action: "brand-delete",
                    csrf_token: csrf_token_default,
                    ItemID: ItemID
                },
                dataType: 'json',
                success: function(response) {
                    closeAllBootstrapModals();
                    document.querySelector("#my-action-confirmation-btn").value = '';
                    document.querySelector('#model-my-action-confirmation-btn').innerHTML = btn;

                    updateCsrfTokens(response.csrf_token);

                    if (response.status === 'true') {
                        location.reload();
                    }
                }
            });
        } else {
            show_action_confirmation_tab(btnClass, 'Delete Brand', 'Delete', 'btn-danger');
        }
    }

    function load_data_list(page = 1) {
        var csrf_token_default = $('input[name="csrf_token_default"]').val();
        var search_input = $('.search_input').val();
        var show_limit = $('.show_limit').val();
        var filter_start = $('#filter-created-from').val();
        var filter_end = $('#filter-created-until').val();

        $(".table-data-list").html(
            '<tr><td colspan="4" class="text-center text-muted"><div class="spinner-border text-primary" style="margin: 50px;"></div></td></tr>'
            );

        $.ajax({
            type: 'POST',
            url: '{{ route('admin.brands.list.ajax') }}',
            data: {
                _token: '{{ csrf_token() }}',
                search_input: search_input,
                show_limit: show_limit,
                page: page,
                filter_start: filter_start,
                filter_end: filter_end
            },
            dataType: 'json',
            success: function(res) {
                let html = '';
                updateCsrfTokens(res.csrf_token);
                if (res.status === 'true') {
                    res.response.forEach(item => {
                        let isSuperUser = {{ $isSuper ? 'true' : 'false' }};
                        let canEditBrand = {{ $canEdit ? 'true' : 'false' }};
                        let canDeleteBrand = {{ $canDelete ? 'true' : 'false' }};
                        let errorSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d63939" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-exclamation-circle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>`;

                        let redirectEdit = '';
                        let redirectDelete = '';

                        // Default brand (DB ID 1) protection
                        let isDefaultBrand = (item.db_id === 1);

                        if (canEditBrand) {
                            if (isDefaultBrand && !isSuperUser) {
                                // Staff cannot edit default brand
                                redirectEdit = `style="cursor:pointer;" onclick="createToast({title:'Permission Denied', description:'You cannot edit the default brand.', svg: errorSvg, timeout: 6000, top: 70})"`;
                                canEditBrand = false; 
                            } else {
                                redirectEdit = `style="cursor:pointer;" onclick="load_content('Edit Brand','<?php echo $site_url . $path_admin; ?>/brands/edit?b_id=${item.id}','nav-item-brands')"`;
                            }
                        } else {
                            // No edit permission
                            redirectEdit = `style="cursor:pointer;" onclick="createToast({title:'Access Denied', description:'You do not have permission to edit brands.', svg: errorSvg, timeout: 6000, top: 70})"`;
                        }

                        if (canDeleteBrand && !isDefaultBrand) {
                            redirectDelete = `onclick="deleteItem('${item.id}')"`;
                        } else {
                            canDeleteBrand = false;
                        }

                        let manageAble = (canEditBrand || canDeleteBrand) ? '' : 'disabled';

                        // Ensure default brand button is disabled for non-super even if permissions exist
                        if (isDefaultBrand && !isSuperUser) {
                            manageAble = 'disabled';
                        }

                        html += `
                            <tr data-id="${item.id}">
                                <td><input class="form-check-input m-0 align-middle table-selectable-check rowCheckbox" type="checkbox" aria-label="Select brand"></td>
                                <td ${redirectEdit}>
                                    <div class="d-flex py-1 align-items-center">
                                        <div class="flex-fill">
                                            <div class="font-weight-medium">${item.identify_name}</div>
                                            <div class="text-secondary">${item.brand_id}</div>
                                        </div>
                                    </div>
                                </td>
                                <td ${redirectEdit}>${item.created_date}</td>
                                <td class="text-end">
                                    <span class="dropdown" style="position: unset;">
                                        <button ${manageAble} class="btn dropdown-toggle align-text-top" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">Actions</button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item ${canEditBrand ? '' : 'd-none'}" href="javascript:void(0)" ${redirectEdit}>Edit</a>
                                            <a class="dropdown-item btnDeleteItem-${item.id} ${canDeleteBrand ? '' : 'd-none'}" href="javascript:void(0)" ${redirectDelete}>Delete</a>
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

                    // Re-init dropdowns for new rows safely
                    if (typeof tabler !== 'undefined' && tabler.bootstrap) {
                        document.querySelectorAll('.table-data-list [data-bs-toggle="dropdown"]').forEach(
                            el => {
                                tabler.bootstrap.Dropdown.getOrCreateInstance(el);
                            });
                    }
                } else {
                    $(".table-data-list").html(
                        '<tr><td colspan="4" class="text-center p-4">No data found</td></tr>');
                }
            }
        });
    }

    function updateCsrfTokens(token) {
        if (!token) return;
        $('input[name="csrf_token"]').val(token);
        $('input[name="csrf_token_default"]').val(token);
    }

    $(document).on('click', '.table-data-list-pagination button', function() {
        load_data_list($(this).data('page'));
    });

    load_data_list(1);

    function filter_hide_show_reset(className) {
        $('.' + className + ' input').val('');
        load_data_list(1);
    }

    $('.filter-tab-data input, .search_input, .show_limit').on('change', function() {
        load_data_list(1);
    });
</script>
