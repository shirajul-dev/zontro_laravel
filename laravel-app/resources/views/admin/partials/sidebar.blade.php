        <div class="offcanvas-md offcanvas-start sidebar" tabindex="-1" id="sidebarMenu">
            <div class="offcanvas-header d-md-none">
                <a href="javascript:void(0)" aria-label="Tabler"
                    onclick="load_content('Dashboard','<?php echo $site_url . $path_admin; ?>/dashboard','nav-menu-dashboard')">
                    <img src="<?= $piprapay_logo_light ?? '' ?>" alt="" style="height: 32px;"
                        onclick="load_content('Dashboard','<?php echo $site_url . $path_admin; ?>/dashboard','nav-menu-dashboard')">
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body p-0">
                <ul class="nav w-100 flex-column gap-2">

                    {{-- Brand Switcher --}}
                    <div class="nav-item dropdown mb-5 mt-2">
                        <a href="#" class="nav-link d-flex lh-1 p-2 rounded" data-bs-toggle="dropdown"
                            aria-label="Open user menu" aria-expanded="false">
                            <span class="avatar avatar-sm"
                                style="min-width: 32px; background-image: url(https://ui-avatars.com/api/?name=<?php echo getNameChars($global_response_brand['response'][0]['identify_name'] ?? 'B', 1); ?>&color=FFFFFF&background=343a40">
                            </span>
                            <div class="ps-2 w-100">
                                <div class="text-black"
                                    style="width: 100px;white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">
                                    <?php echo $global_response_brand['response'][0]['identify_name'] ?? 'No Brand'; ?></div>
                                <div class="mt-1 small text-secondary">Active brand</div>
                            </div>

                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-caret-down">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M18 9c.852 0 1.297 .986 .783 1.623l-.076 .084l-6 6a1 1 0 0 1 -1.32 .083l-.094 -.083l-6 -6l-.083 -.094l-.054 -.077l-.054 -.096l-.017 -.036l-.027 -.067l-.032 -.108l-.01 -.053l-.01 -.06l-.004 -.057v-.118l.005 -.058l.009 -.06l.01 -.052l.032 -.108l.027 -.067l.07 -.132l.065 -.09l.073 -.081l.094 -.083l.077 -.054l.096 -.054l.036 -.017l.067 -.027l.108 -.032l.053 -.01l.06 -.01l.057 -.004l12.059 -.002z" />
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow w-100">
                            <?php
                      $response_permission = json_decode(getData($db_prefix.'permission','WHERE a_id = "'.$global_user_response['response'][0]['a_id'].'" AND status = "active" AND brand_id != "'.$global_response_permission['response'][0]['brand_id'].'"'),true);
                      if($response_permission['status'] == true){
                          foreach($response_permission['response'] as $row){
                              $response_brand = json_decode(getData($db_prefix.'brands','WHERE brand_id = "'.$row['brand_id'].'"'),true);
                        ?>
                            <a href="javascript:void(0)" class="dropdown-item"
                                onclick="set_brand('<?php echo $row['brand_id']; ?>')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-building-store">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3 21l18 0" />
                                    <path
                                        d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l2 4" />
                                    <path d="M5 21l0 -10.15" />
                                    <path d="M19 21l0 -10.15" />
                                    <path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4" />
                                </svg>
                                <?php echo $response_brand['response'][0]['identify_name']; ?>
                            </a>
                            <?php
                          }
                            ?>
                                      <div class="dropdown-divider"></div>
                                      <?php
                                }
                            ?>

                            <a href="javascript:void(0)"
                                class="dropdown-item <?= hasPermission(json_decode($global_response_permission['response'][0]['permission'], true), 'brands', 'create', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                                onclick="load_content('Create New Brand','<?php echo $site_url . $path_admin; ?>/brands/create','nav-item-brands')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 5l0 14" />
                                    <path d="M5 12l14 0" />
                                </svg>
                                Create New
                            </a>
                        </div>
                    </div>
                    {{-- End Brand Switcher --}}

                    <!-- Dashboard -->
                    <li class="nav-item nav-item-dashboard <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'dashboard', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Dashboard','<?php echo $site_url . $path_admin; ?>/dashboard','nav-item-dashboard')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-home">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                                    <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                                </svg></span>
                            <span class="nav-link-title ms-2">Dashboard</span>
                        </a>
                    </li>

                    <!-- Reports -->
                    <li class="nav-item nav-item-reports <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'reports', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Reports','<?php echo $site_url . $path_admin; ?>/reports','nav-item-reports')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-chart-pie-2">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 3v9h9" />
                                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                </svg></span>
                            <span class="nav-link-title ms-2">Reports</span>
                        </a>
                    </li>

                    <!-- Gateways -->
                    <li class="nav-item nav-item-gateways <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'gateways', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Gateways','<?php echo $site_url . $path_admin; ?>/gateways','nav-item-gateways')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-wallet">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" />
                                    <path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" />
                                </svg></span>
                            <span class="nav-link-title ms-2">Gateways</span>
                        </a>
                    </li>

                    <!-- Customers -->
                    <li class="nav-item nav-item-customers <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'customers', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Customers','<?php echo $site_url . $path_admin; ?>/customers','nav-item-customers')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-users">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                    <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                                </svg></span>
                            <span class="nav-link-title ms-2">Customers</span>
                        </a>
                    </li>

                    <!-- Transaction -->
                    <li class="nav-item nav-item-transaction <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'transaction', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Transaction','<?php echo $site_url . $path_admin; ?>/transaction','nav-item-transaction')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-receipt">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2m4 -14h6m-6 4h6m-2 4h2" />
                                </svg></span>
                            <span class="nav-link-title ms-2">Transaction</span>
                            <?php
                            $count = 0;
                            $activeBrandId = $global_response_brand['response'][0]['brand_id'] ?? null;
                            if ($activeBrandId) {
                                $response_dashboard_info = json_decode(getData($db_prefix . 'transaction', ' WHERE brand_id = "' . $activeBrandId . '" AND status = "pending"'), true);
                                if ($response_dashboard_info['status'] == true) {
                                    foreach ($response_dashboard_info['response'] as $row) {
                                        $count++;
                                    }
                                }
                            }
                            ?>
                            <span
                                class="badge bg-danger rounded-pill <?= $count == 0 ? 'd-none' : '' ?> ms-auto text-white"><?php echo number_format($count, 0); ?></span>
                        </a>
                    </li>

                    <!-- Invoice -->
                    <li class="nav-item nav-item-invoice <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'invoice', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Invoice','<?php echo $site_url . $path_admin; ?>/invoice','nav-item-invoice')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-invoice">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                    <path
                                        d="M19 12v7a1.78 1.78 0 0 1 -3.1 1.4a1.65 1.65 0 0 0 -2.6 0a1.65 1.65 0 0 1 -2.6 0a1.65 1.65 0 0 0 -2.6 0a1.78 1.78 0 0 1 -3.1 -1.4v-14a2 2 0 0 1 2 -2h7l5 5v4.25" />
                                </svg></span>
                            <span class="nav-link-title ms-2">Invoice</span>
                        </a>
                    </li>

                    <!-- Payment Link -->
                    <li class="nav-item nav-item-payment-link <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'payment_link', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Payment Link','<?php echo $site_url . $path_admin; ?>/payment-link','nav-item-payment-link')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-link">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M9 15l6 -6" />
                                    <path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464" />
                                    <path
                                        d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463" />
                                </svg></span>
                            <span class="nav-link-title ms-2">Payment Link</span>
                        </a>
                    </li>

                    <!-- Appearance Heading -->
                    <li class="card-title pt-3">Appearance</li>

                    <!-- Brand Settings -->
                    <li class="nav-item nav-item-brand-setting <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'brand_settings', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Brand Settings','<?php echo $site_url . $path_admin; ?>/brand-setting','nav-item-brand-setting')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-building-cog">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3 21h9" />
                                    <path d="M9 8h1" />
                                    <path d="M9 12h1" />
                                    <path d="M9 16h1" />
                                    <path d="M14 8h1" />
                                    <path d="M14 12h1" />
                                    <path
                                        d="M5 21v-16c0 -.53 .211 -1.039 .586 -1.414c.375 -.375 .884 -.586 1.414 -.586h10c.53 0 1.039 .211 1.414 .586c.375 .375 .586 .884 .586 1.414v7" />
                                    <path
                                        d="M16 18c0 .53 .211 1.039 .586 1.414c.375 .375 .884 .586 1.414 .586c.53 0 1.039 -.211 1.414 -.586c.375 -.375 .586 -.884 .586 -1.414c0 -.53 -.211 -1.039 -.586 -1.414c-.375 -.375 -.884 -.586 -1.414 -.586c-.53 0 -1.039 .211 -1.414 .586c-.375 .375 -.586 .884 -.586 1.414z" />
                                    <path d="M18 14.5v1.5" />
                                    <path d="M18 20v1.5" />
                                    <path d="M21.032 16.25l-1.299 .75" />
                                    <path d="M16.27 19l-1.3 .75" />
                                    <path d="M14.97 16.25l1.3 .75" />
                                    <path d="M19.733 19l1.3 .75" />
                                </svg></span>
                            <span class="nav-link-title ms-2">Brand Settings</span>
                        </a>
                    </li>

                    <!-- MFS Automation Heading -->
                    <li class="card-title pt-3">MFS Automation</li>

                    <!-- SMS Data -->
                    <li class="nav-item nav-item-sms-data <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'sms_data', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('SMS Data','<?php echo $site_url . $path_admin; ?>/sms-data','nav-item-sms-data')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-cloud-computing">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M6.657 16c-2.572 0 -4.657 -2.007 -4.657 -4.483c0 -2.475 2.085 -4.482 4.657 -4.482c.393 -1.762 1.794 -3.2 3.675 -3.773c1.88 -.572 3.956 -.193 5.444 1c1.488 1.19 2.162 3.007 1.77 4.769h.99c1.913 0 3.464 1.56 3.464 3.486c0 1.927 -1.551 3.487 -3.465 3.487h-11.878" />
                                    <path d="M12 16v5" />
                                    <path d="M16 16v4a1 1 0 0 0 1 1h4" />
                                    <path d="M8 16v4a1 1 0 0 1 -1 1h-4" />
                                </svg></span>
                            <span class="nav-link-title ms-2">SMS Data</span>
                        </a>
                    </li>

                    <!-- Devices -->
                    <li class="nav-item nav-item-devices <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'device', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Devices','<?php echo $site_url . $path_admin; ?>/devices','nav-item-devices')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-device-mobile">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M6 5a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2v-14z" />
                                    <path d="M11 4h2" />
                                    <path d="M12 17v.01" />
                                </svg></span>
                            <span class="nav-link-title ms-2">Devices</span>
                        </a>
                    </li>

                    @if ($isSuperAdmin)
                        <!-- Management Heading -->
                        <li class="card-title pt-3">Management</li>

                        <!-- Merchants -->
                        <li class="nav-item nav-item-merchants <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'merchants', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                            onclick="load_content('Merchants','<?php echo $site_url . $path_admin; ?>/merchants','nav-item-merchants')">
                            <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                                <span class="nav-link-icon d-inline-flex align-items-center justify-content-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-building-community">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M8 9l5 5v7h-5v-4m0 4h-5v-7l5 -5m1 1v-2a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v7h3a1 1 0 0 1 1 1v5a1 1 0 0 1 -1 1h-7a1 1 0 0 1 -1 -1v-3a1 1 0 0 0 -1 -1h-1" />
                                    </svg>
                                </span>
                                <span class="nav-link-title ms-2">Merchants</span>
                            </a>
                        </li>
                    @endif

                    <!-- Administration Heading -->
                    <li class="card-title pt-3">Administration</li>

                    <!-- Addons -->
                    <li class="nav-item nav-item-addons <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'addons', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Addons','<?php echo $site_url . $path_admin; ?>/addons','nav-item-addons')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-puzzle">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M4 7h3a1 1 0 0 0 1 -1v-1a2 2 0 0 1 4 0v1a1 1 0 0 0 1 1h3a1 1 0 0 1 1 1v3a1 1 0 0 0 1 1h1a2 2 0 0 1 0 4h-1a1 1 0 0 0 -1 1v3a1 1 0 0 1 -1 1h-3a1 1 0 0 1 -1 -1v-1a2 2 0 0 0 -4 0v1a1 1 0 0 1 -1 1h-3a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1h1a2 2 0 0 0 0 -4h-1a1 1 0 0 1 -1 -1v-3a1 1 0 0 1 1 -1" />
                                </svg></span>
                            <span class="nav-link-title ms-2">Addons</span>
                        </a>
                    </li>

                    <!-- Domains -->
                    <li class="nav-item nav-item-domains <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'domains', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Domains','<?php echo $site_url . $path_admin; ?>/domains','nav-item-domains')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-world-www">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M19.5 7a9 9 0 0 0 -7.5 -4a8.991 8.991 0 0 0 -7.484 4" />
                                    <path d="M11.5 3a16.989 16.989 0 0 0 -1.826 4" />
                                    <path d="M12.5 3a16.989 16.989 0 0 1 1.828 4" />
                                    <path d="M19.5 17a9 9 0 0 1 -7.5 4a8.991 8.991 0 0 1 -7.484 -4" />
                                    <path d="M11.5 21a16.989 16.989 0 0 1 -1.826 -4" />
                                    <path d="M12.5 21a16.989 16.989 0 0 0 1.828 -4" />
                                    <path d="M2 10l1 4l1.5 -4l1.5 4l1 -4" />
                                    <path d="M17 10l1 4l1.5 -4l1.5 4l1 -4" />
                                    <path d="M9.5 10l1 4l1.5 -4l1.5 4l1 -4" />
                                </svg></span>
                            <span class="nav-link-title ms-2">Domains</span>
                        </a>
                    </li>

                    <!-- Brands -->
                    <li class="nav-item nav-item-brands <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'brands', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Brands','<?php echo $site_url . $path_admin; ?>/brands','nav-item-brands')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-building-store">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3 21l18 0" />
                                    <path
                                        d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l2 4" />
                                    <path d="M5 21l0 -10.15" />
                                    <path d="M19 21l0 -10.15" />
                                    <path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4" />
                                </svg></span>
                            <span class="nav-link-title ms-2">All Brands</span>
                        </a>
                    </li>

                    <!-- Staff Management -->
                    <li class="nav-item nav-item-staff-management <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'staff_management', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                        onclick="load_content('Staff Management','<?php echo $site_url . $path_admin; ?>/staff-management','nav-item-staff-management')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-password-user">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 17v4" />
                                    <path d="M10 20l4 -2" />
                                    <path d="M10 18l4 2" />
                                    <path d="M5 17v4" />
                                    <path d="M3 20l4 -2" />
                                    <path d="M3 18l4 2" />
                                    <path d="M19 17v4" />
                                    <path d="M17 20l4 -2" />
                                    <path d="M17 18l4 2" />
                                    <path d="M9 6a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                    <path d="M7 14a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2" />
                                </svg></span>
                            <span class="nav-link-title ms-2">Staff Management</span>
                        </a>
                    </li>

                    @if ($isSuperAdmin)
                        <!-- System Settings -->
                        <li class="nav-item nav-item-system-settings <?= canAccessPage(json_decode($global_response_permission['response'][0]['permission'], true), 'system_settings', $global_user_response['response'][0]['role']) ? '' : 'd-none' ?>"
                            onclick="load_content('System Settings','<?php echo $site_url . $path_admin; ?>/system-settings','nav-item-system-settings')">
                            <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                                <span
                                    class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-settings">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                        <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                    </svg></span>
                                <span class="nav-link-title ms-2">System Settings</span>
                            </a>
                        </li>
                    @endif

                    <!-- Activities -->
                    <li class="nav-item nav-item-activities"
                        onclick="load_content('Activities','<?php echo $site_url . $path_admin; ?>/activities','nav-item-activities')">
                        <a href="javascript:void(0)" class="nav-link d-flex align-items-center rounded">
                            <span class="nav-link-icon d-inline-flex align-items-center justify-content-center"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-activity">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M3 12h4l3 8l4 -16l3 8h4"></path>
                                </svg></span>
                            <span class="nav-link-title ms-2">Activities</span>
                        </a>
                    </li>

                </ul>
            </div>
        </div>
