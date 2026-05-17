<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title', config('app.name')) | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('assets/images/favicon-dark.png') }}">
    <link href="{{ m_asset('assets/css/style.css') }}" rel="stylesheet">

    @stack('styles')
    <link rel="stylesheet" href="{{ m_asset('assets/css/toastr.min.css') }}">
</head>

<body x-data="{ page: 'ecommerce', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false }" x-init="darkMode = JSON.parse(localStorage.getItem('darkMode'));
$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))" :class="{ 'dark bg-gray-900': darkMode === true }">
    <!-- ===== Preloader Start ===== -->
    <div x-show="loaded" x-init="window.addEventListener('DOMContentLoaded', () => { setTimeout(() => loaded = false, 500) })"
        class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black">
        <x-loader show-text="true" text="Initializing Dashboard..." />
    </div>

    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">
        <!-- ===== Sidebar Start ===== -->
        @include('merchant.default.partials.sidebar')
        <!-- ===== Content Area Start ===== -->
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
            <!-- Small Device Overlay Start -->
            <div @click="sidebarToggle = false" :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                class="fixed w-full h-screen z-9 bg-gray-900/50"></div>
            <!-- Small Device Overlay End -->

            <!-- ===== Header Start ===== -->
            @include('merchant.default.partials.header')
            <!-- ===== Main Content Start ===== -->
            <main class="flex flex-col flex-1">
                @yield('content')
            </main>
            <!-- ===== Main Content End ===== -->
        </div>
        <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->
    <script defer src="{{ m_asset('assets/js/bundle.js') }}"></script>
    <script src="{{ m_asset('assets/js/toastr.min.js') }}"></script>

    {{-- Start Toast --}}
    <script>
        function showToast(type, message) {
            toastr.options = {
                "duration": 3000,
                "animationDuration": 400,
                "progressBar": true,
                "autoClose": true,
                "closeButton": true,
                "closeButtonIcon": "csm-toast-close-icon",
                "positionClass": "csm-toast-top-right",
                "showIcon": true,
                "preventDuplicates": true,
                "icons": {
                    "info": "csm-toast-info-icon",
                    "warning": "csm-toast-warning-icon",
                    "success": "csm-toast-success-icon",
                    "error": "csm-toast-error-icon"
                },
                "colorsClasses": {
                    "info": "csm-toast-info",
                    "warning": "csm-toast-warning",
                    "success": "csm-toast-success",
                    "error": "csm-toast-error"
                }
            };

            switch (type) {
                case 'success':
                    toastr.success(message);
                    break;
                case 'error':
                    toastr.error(message);
                    break;
                default:
                    toastr.info(message);
                    break;
            }
        }
    </script>
    {{-- End Toast --}}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('main');

            // Intercept clicks on internal links
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript') ||
                    link.getAttribute('target') === '_blank' ||
                    link.hasAttribute('data-no-pjax')) return;

                try {
                    const url = new URL(href, window.location.origin);
                    if (url.origin === window.location.origin && href !== '#') {
                        e.preventDefault();
                        loadPage(href);
                    }
                } catch (e) {}
            });

            window.addEventListener('popstate', function() {
                loadPage(window.location.href, false);
            });

            async function loadPage(url, pushState = true) {
                // Show loader and center it
                mainContent.classList.add('justify-center');
                mainContent.innerHTML = `<x-loader show-text="true" text="" />`;

                try {
                    const response = await fetch(url);
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newMain = doc.querySelector('main');
                    const newTitle = doc.querySelector('title');

                    if (newMain) {
                        mainContent.classList.remove('justify-center');
                        mainContent.innerHTML = newMain.innerHTML;
                        if (newTitle) document.title = newTitle.innerText;

                        if (pushState) {
                            window.history.pushState({}, '', url);
                        }

                        // Re-run any scripts that were in the new content
                        mainContent.querySelectorAll('script').forEach(oldScript => {
                            const newScript = document.createElement('script');
                            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                            newScript.text = oldScript.text;
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        });

                        // Re-initialize Alpine components in the newly loaded page
                        if (window.Alpine) {
                            window.Alpine.initTree(mainContent);
                        }

                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    } else {
                        window.location.href = url;
                    }
                } catch (error) {
                    console.error('Navigation error:', error);
                    window.location.href = url;
                }
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
