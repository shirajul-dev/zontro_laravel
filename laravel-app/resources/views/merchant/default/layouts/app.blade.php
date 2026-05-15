<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }} Merchant</title>
    <link rel="icon" href="{{ asset('assets/images/favicon-light.png') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="{{ m_asset('assets/css/style.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Outfit', sans-serif; }
        [x-cloak] { display: none !important; }
        
        /* Smooth transition for sidebar */
        .sidebar-transition { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb { background: #374151; }
    </style>

    @stack('styles')
</head>

<body x-data="{ 
        sidebarOpen: false, 
        darkMode: JSON.parse(localStorage.getItem('darkMode')) || false,
        profileDropdown: false
    }" 
    x-init="$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{ 'dark': darkMode }"
    class="bg-gray-50 text-gray-900 dark:bg-gray-950 dark:text-white/90 antialiased"
>
    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">

        <!-- ===== Sidebar Start ===== -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-gray-200 dark:bg-gray-900 dark:border-gray-800 lg:static lg:translate-x-0 sidebar-transition flex flex-col"
        >
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between px-8 py-6">
                <a href="{{ route('merchant.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/favicon-dark.png') }}" class="w-10 h-10 rounded-xl shadow-lg" alt="Logo">
                    <span class="text-xl font-bold tracking-tight text-gray-800 dark:text-white">{{ config('app.name') }}</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="flex-1 px-4 py-4 overflow-y-auto space-y-1">
                <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Main Menu</p>
                
                <a href="{{ route('merchant.dashboard') }}" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 @if(request()->routeIs('merchant.dashboard')) bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-semibold @else text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5 @endif"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Dashboard</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5 transition-all duration-200">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <span>Payments</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5 transition-all duration-200">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <span>Analytics</span>
                </a>

                <p class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-8 mb-4">Account</p>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5 transition-all duration-200">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span>Settings</span>
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-6 border-t border-gray-200 dark:border-gray-800">
                <div class="bg-brand-500 rounded-2xl p-5 text-white shadow-xl shadow-brand-500/20">
                    <p class="text-xs font-medium opacity-80 mb-1">Upgrade to</p>
                    <h5 class="text-lg font-bold mb-3">Enterprise Plan</h5>
                    <button class="w-full py-2 bg-white text-brand-600 rounded-xl text-xs font-bold hover:bg-gray-50 transition-colors">Upgrade Now</button>
                </div>
            </div>
        </aside>

        <!-- Overlay -->
        <div 
            x-show="sidebarOpen" 
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden"
            x-cloak
        ></div>

        <!-- ===== Main Content Start ===== -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Header -->
            <header class="flex items-center justify-between px-8 py-4 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-200 dark:border-gray-800 sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                    </button>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white hidden sm:block">@yield('title')</h2>
                </div>

                <div class="flex items-center gap-3 sm:gap-6">
                    <!-- Dark Mode Toggle -->
                    <button 
                        @click="darkMode = !darkMode"
                        class="p-2.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl transition-all"
                    >
                        <svg x-show="!darkMode" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                        <svg x-show="darkMode" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                    </button>

                    <!-- Notifications -->
                    <button class="relative p-2.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl transition-all">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-error-500 rounded-full border-2 border-white dark:border-gray-900"></span>
                    </button>

                    <!-- User Profile -->
                    <div class="relative">
                        <button 
                            @click="profileDropdown = !profileDropdown"
                            @click.away="profileDropdown = false"
                            class="flex items-center gap-3 p-1 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition-all"
                        >
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->full_name) }}&background=6366f1&color=fff" class="w-10 h-10 rounded-xl shadow-md" alt="Avatar">
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-bold text-gray-800 dark:text-white">{{ auth()->user()->full_name }}</p>
                                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-widest">{{ auth()->user()->username }}</p>
                            </div>
                        </button>

                        <div 
                            x-show="profileDropdown" 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-3 w-56 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-2xl overflow-hidden z-50"
                            x-cloak
                        >
                            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-800">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-1">Signed in as</p>
                                <p class="text-sm font-bold truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="p-2">
                                <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm text-gray-500 hover:bg-gray-50 dark:hover:bg-white/5 rounded-xl transition-colors">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    Profile
                                </a>
                                <form action="{{ route('merchant.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm text-error-500 hover:bg-error-50 dark:hover:bg-error-500/10 rounded-xl transition-colors">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto p-6 sm:p-8 bg-gray-50 dark:bg-gray-950">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    <!-- ===== Page Wrapper End ===== -->

    <script defer src="{{ m_asset('assets/js/bundle.js') }}"></script>
    @stack('scripts')
</body>

</html>
