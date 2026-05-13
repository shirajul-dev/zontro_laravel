<div class="kt-sidebar dark bg-background border-e border-e-border fixed top-0 bottom-0 z-20 hidden lg:flex flex-col items-stretch shrink-0 [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]" data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start top-0 bottom-0" id="sidebar">
    <div class="kt-sidebar-header hidden lg:flex items-center relative justify-between px-3 lg:px-6 shrink-0" id="sidebar_header">
        <div class="kt-sidebar-logo min-w-0">
            <a href="{{ route('superadmin.dashboard') }}">
                <img class="default-logo min-h-[22px] max-w-none dark:hidden" src="{{ asset('assets/superadmin/media/app/default-logo.svg') }}"/>
                <img class="default-logo min-h-[22px] max-w-none hidden dark:block" src="{{ asset('assets/superadmin/media/app/default-logo-dark.svg') }}"/>
                <img class="small-logo min-h-[22px] max-w-none" src="{{ asset('assets/superadmin/media/app/mini-logo.svg') }}"/>
            </a>
        </div>
        <div data-kt-toggle="body" data-kt-toggle-class="kt-sidebar-collapse" id="sidebar_toggle">
            <div class="hidden dark:block">
                <button class="kt-btn kt-btn-outline kt-btn-icon size-[30px] bg-white border border-white hover:[&_i]:text-black/80 [&_border]:[&_i]:text-black/80 border border-black/10! absolute start-full top-2/4 z-40 -translate-x-2/4 -translate-y-2/4 rtl:translate-x-2/4">
                    <i class="ki-filled ki-black-left-line kt-toggle-active:rotate-180 transition-all duration-300 rtl:translate rtl:rotate-180 rtl:kt-toggle-active:rotate-0"></i>
                </button>
            </div>
            <div class="dark:hidden light">
                <button class="kt-btn kt-btn-outline kt-btn-icon size-[30px] rounded-lg absolute start-full top-2/4 z-40 -translate-x-2/4 -translate-y-2/4 rtl:translate-x-2/4">
                    <i class="ki-filled ki-black-left-line kt-toggle-active:rotate-180 transition-all duration-300 rtl:translate rtl:rotate-180 rtl:kt-toggle-active:rotate-0"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="kt-sidebar-content flex grow shrink-0 py-5 pe-2" id="sidebar_content">
        <div class="kt-scrollable-y-hover grow shrink-0 flex ps-2 lg:ps-5 pe-1 lg:pe-3" data-kt-scrollable="true" data-kt-scrollable-dependencies="#sidebar_header" data-kt-scrollable-height="auto" data-kt-scrollable-offset="0px" data-kt-scrollable-wrappers="#sidebar_content" id="sidebar_scrollable">
            <!-- Sidebar Menu -->
            <div class="kt-menu flex flex-col grow gap-1" data-kt-menu="true" data-kt-menu-accordion-expand-all="false" id="sidebar_menu">
                <!-- Dashboard -->
                <div class="kt-menu-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                    <a class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px]" href="{{ route('superadmin.dashboard') }}">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-element-11 text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">
                            Dashboard
                        </span>
                    </a>
                </div>

                <!-- User Section -->
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span class="kt-menu-heading uppercase text-xs font-medium text-muted-foreground ps-[10px] pe-[10px]">
                        User
                    </span>
                </div>

                <!-- Merchant Menu (New) -->
                <div class="kt-menu-item {{ request()->routeIs('superadmin.merchants.*') ? 'active' : '' }}">
                    <a class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px]" href="{{ route('superadmin.merchants.index') }}">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-users text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">
                            Merchant
                        </span>
                    </a>
                </div>

                <!-- Public Profile -->
                <div class="kt-menu-item" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px]" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-profile-circle text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">
                            Public Profile
                        </span>
                        <span class="kt-menu-arrow text-muted-foreground w-[20px] shrink-0 justify-end ms-1 me-[-10px]">
                            <span class="inline-flex kt-menu-item-show:hidden">
                                <i class="ki-filled ki-plus text-[11px]"></i>
                            </span>
                            <span class="hidden kt-menu-item-show:inline-flex">
                                <i class="ki-filled ki-minus text-[11px]"></i>
                            </span>
                        </span>
                    </div>
                    <div class="kt-menu-accordion gap-1 ps-[10px] relative before:absolute before:start-[20px] before:top-0 before:bottom-0 before:border-s before:border-border">
                        <div class="kt-menu-item">
                            <a class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[14px] ps-[10px] pe-[10px] py-[8px]" href="#">
                                <span class="kt-menu-bullet flex w-[6px] -start-[3px] rtl:start-0 relative before:absolute before:top-0 before:size-[6px] before:rounded-full rtl:before:translate-x-1/2 before:-translate-y-1/2 kt-menu-item-active:before:bg-primary kt-menu-item-hover:before:bg-primary"></span>
                                <span class="kt-menu-title text-2sm font-normal text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary">
                                    Profiles
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Management Section -->
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span class="kt-menu-heading uppercase text-xs font-medium text-muted-foreground ps-[10px] pe-[10px]">
                        Management
                    </span>
                </div>

                <!-- Plans Menu -->
                <div class="kt-menu-item {{ request()->routeIs('superadmin.plans.*') ? 'active' : '' }}">
                    <a class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px]" href="{{ route('superadmin.plans.index') }}">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-crown text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">
                            Plans
                        </span>
                    </a>
                </div>

                <!-- My Account -->
                <div class="kt-menu-item" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px]" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-setting-2 text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">
                            My Account
                        </span>
                        <span class="kt-menu-arrow text-muted-foreground w-[20px] shrink-0 justify-end ms-1 me-[-10px]">
                            <span class="inline-flex kt-menu-item-show:hidden">
                                <i class="ki-filled ki-plus text-[11px]"></i>
                            </span>
                            <span class="hidden kt-menu-item-show:inline-flex">
                                <i class="ki-filled ki-minus text-[11px]"></i>
                            </span>
                        </span>
                    </div>
                    <div class="kt-menu-accordion gap-1 ps-[10px] relative before:absolute before:start-[20px] before:top-0 before:bottom-0 before:border-s before:border-border">
                        <!-- Add account items here if needed -->
                    </div>
                </div>

                <!-- Network -->
                <div class="kt-menu-item" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px]" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-users text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">
                            Network
                        </span>
                        <span class="kt-menu-arrow text-muted-foreground w-[20px] shrink-0 justify-end ms-1 me-[-10px]">
                            <span class="inline-flex kt-menu-item-show:hidden">
                                <i class="ki-filled ki-plus text-[11px]"></i>
                            </span>
                            <span class="hidden kt-menu-item-show:inline-flex">
                                <i class="ki-filled ki-minus text-[11px]"></i>
                            </span>
                        </span>
                    </div>
                    <div class="kt-menu-accordion gap-1 ps-[10px] relative before:absolute before:start-[20px] before:top-0 before:bottom-0 before:border-s before:border-border">
                        <!-- Add network items here if needed -->
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
