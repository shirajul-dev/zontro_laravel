<meta charset="utf-8" />
<meta content="follow, index" name="robots" />
<meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport" />
<meta content="SuperAdmin Dashboard" name="description" />
<link href="{{ asset('assets/superadmin/media/app/apple-touch-icon.png') }}" rel="apple-touch-icon" sizes="180x180" />
<link href="{{ asset('assets/superadmin/media/app/favicon-32x32.png') }}" rel="icon" sizes="32x32"
    type="image/png" />
<link href="{{ asset('assets/superadmin/media/app/favicon-16x16.png') }}" rel="icon" sizes="16x16"
    type="image/png" />
<link href="{{ asset('assets/superadmin/media/app/favicon.ico') }}" rel="shortcut icon" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
<link href="{{ asset('assets/superadmin/vendors/apexcharts/apexcharts.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/superadmin/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/superadmin/css/styles.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/superadmin/css/dash.css') }}" rel="stylesheet" />

<script>
    const defaultThemeMode = 'light'; // light|dark|system
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
