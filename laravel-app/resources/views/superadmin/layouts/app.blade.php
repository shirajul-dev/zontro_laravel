<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" dir="ltr" lang="en">

<head>
    @include('superadmin.partials._head')
    @yield('styles')
</head>

<body class="antialiased flex h-full text-base text-foreground bg-background demo1 kt-sidebar-fixed kt-header-fixed"
    id="body">
    <!-- Main -->
    <div class="flex grow">
        @include('superadmin.partials._sidebar')

        <!-- Wrapper -->
        <div class="kt-wrapper flex grow flex-col lg:ps-[--kt-sidebar-width]">
            @include('superadmin.partials._header')

            <!-- Content -->
            <main class="grow pt-5" id="content" role="content">
                @yield('content')
            </main>
            <!-- End of Content -->

            @include('superadmin.partials._footer')
        </div>
        <!-- End of Wrapper -->
    </div>
    <!-- End of Main -->

    @include('superadmin.partials._scripts')
    @yield('scripts')
    @stack('scripts')
</body>

</html>
