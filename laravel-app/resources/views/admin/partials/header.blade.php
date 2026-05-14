        <header class="navbar navbar-expand-md sticky-top d-print-none py-2">
            <div class="container-xl">
                <!-- BEGIN NAVBAR TOGGLER -->
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
                    aria-controls="sidebarMenu"> <span class="navbar-toggler-icon"></span> </button>

                <!-- END NAVBAR TOGGLER -->
                <!-- BEGIN NAVBAR LOGO -->
                <div class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                    <a href="javascript:void(0)" aria-label="Tabler">
                        <img src="{{ asset('assets/images/logo-light.png') }}" alt="" style="height: 32px;"
                            onclick="load_content('Dashboard','<?php echo $site_url . $path_admin; ?>/dashboard','nav-menu-dashboard')">
                    </a>
                </div>
                <!-- END NAVBAR LOGO -->
                <div class="navbar-nav flex-row order-md-last">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown"
                            aria-label="Open user menu" aria-expanded="false">
                            <span class="avatar avatar-sm"
                                style="background-image: url(https://ui-avatars.com/api/?name=<?php echo getNameChars($global_user_response['response'][0]['full_name'] ?? 'U', 2); ?>&color=FFFFFF&background=343a40">
                            </span>
                            <div class="d-none d-xl-block ps-2">
                                <div
                                    style="width: 100px;white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">
                                    {{ Auth::user()->full_name ?? 'User' }}</div>
                                <div class="mt-1 small text-secondary">
                                    {{ ucfirst(Auth::user()->role ?? 'staff') }}</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="javascript:void(0)" class="dropdown-item"
                                onclick="load_content('My Account','<?php echo $site_url . $path_admin; ?>/my-account','nav-menu-my-account')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-user">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                </svg>
                                My Account
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item"
                                onclick="load_content('Activities','<?php echo $site_url . $path_admin; ?>/activities','nav-item-activities')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-activity">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3 12h4l3 8l4 -16l3 8h4" />
                                </svg>
                                Activities
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo $site_url . $path_admin; ?>/logout" class="dropdown-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-logout">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
                                    <path d="M9 12h12l-3 -3" />
                                    <path d="M18 15l3 -3" />
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
