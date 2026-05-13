<!doctype html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="SuperAdmin" />
    <title>@yield('title', 'SuperAdmin')</title>

    <link rel="icon" href="{{ asset('assets/superadmin/media/app/favicon-32x32.png') }}" sizes="32x32" type="image/png" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="{{ asset('assets/superadmin/vendors/apexcharts/apexcharts.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/superadmin/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/superadmin/css/styles.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/superadmin/css/index.css') }}" rel="stylesheet"/>
    <!-- Notyf Toast -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

    <style>
        .page-bg { background-image: url('{{ asset('assets/superadmin/media/images/2600x1200/bg-10.png') }}'); }
        .dark .page-bg { background-image: url('{{ asset('assets/superadmin/media/images/2600x1200/bg-10-dark.png') }}'); }
    </style>

    @stack('head')
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background">
    <script>
        const defaultThemeMode = 'light';
        let themeMode;
        if (document.documentElement) {
            if (localStorage.getItem('kt-theme')) {
                themeMode = localStorage.getItem('kt-theme');
            } else if (document.documentElement.hasAttribute('data-kt-theme-mode')) {
                themeMode = document.documentElement.getAttribute('data-kt-theme-mode');
            } else {
                themeMode = defaultThemeMode;
            }
            if (themeMode === 'system') {
                themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.classList.add(themeMode);
        }
    </script>

    @yield('content')

    <script src="{{ asset('assets/superadmin/js/core.bundle.js') }}"></script>
    <script src="{{ asset('assets/superadmin/vendors/ktui/ktui.min.js') }}"></script>
    <script src="{{ asset('assets/superadmin/vendors/apexcharts/apexcharts.min.js') }}"></script>
    <!-- Notyf Toast JS -->
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script>
        // Initialize Notyf globally
        window.notyf = new Notyf({
            duration: 4000,
            position: { x: 'right', y: 'top' },
            types: [
                {
                    type: 'success',
                    background: '#10b981',
                    icon: { className: 'notyf__icon', tagName: 'i' }
                },
                {
                    type: 'error',
                    background: '#ef4444',
                    icon: { className: 'notyf__icon', tagName: 'i' }
                }
            ]
        });
    </script>
    @stack('scripts')
</body>
</html>
