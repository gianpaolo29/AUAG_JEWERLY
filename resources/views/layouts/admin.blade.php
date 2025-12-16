@props(['title' => 'Admin Dashboard'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title }} | AUAG Admin</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('Auag.jpg') }}">


    {{-- Third-party CSS --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.5.1/flowbite.min.css" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    {{-- Your app (Tailwind etc.) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- SweetAlert2 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    {{-- JS libraries --}}
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/flowbite@2.3.0/dist/flowbite.min.js"></script>

    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')

    <style>
        [x-cloak] { display: none !important; }

        /* Custom Scrollbar for light theme */
        .sidebar-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        .sidebar-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .sidebar-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .sidebar-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .main-content-transition {
            transition: padding-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Gold accent gradients */
        .gold-gradient {
            background: linear-gradient(135deg, #fde68a 0%, #fbbf24 100%);
        }
        .gold-gradient-light {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }
        .gold-gradient-dark {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        /* Card hover effects */
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Smooth animations */
        .smooth-transition {
            transition: all 0.3s ease;
        }

        /* Luxury modal styling */
        .luxury-modal {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(245, 158, 11, 0.3);
            background: linear-gradient(145deg, #111827 0%, #1f2937 100%);
        }
    </style>
</head>
<body class="h-full antialiased bg-gray-50"
    x-data="{
        isSidebarOpen: false,
        isDesktopSidebarCollapsed: localStorage.getItem('isDesktopSidebarCollapsed') === 'true',
        activeTooltip: null,
        tooltipText: '',
        tooltipPosition: { x: 0, y: 0 },
        logoutModalOpen: false,
        openLogoutModal() {
            this.logoutModalOpen = true;
        }
    }"
    x-init="$watch('isDesktopSidebarCollapsed', value => localStorage.setItem('isDesktopSidebarCollapsed', value))"
>
    {{-- Mobile sidebar (off-canvas) --}}
    <div x-show="isSidebarOpen" class="relative z-50 lg:hidden" x-cloak>
        {{-- Backdrop --}}
        <div x-show="isSidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="isSidebarOpen = false"
             class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm">
        </div>

        {{-- Sidebar panel --}}
        <div x-show="isSidebarOpen"
             x-transition:enter="transition-transform ease-in-out duration-300"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition-transform ease-in-out duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 z-50 w-full max-w-xs">

            {{-- Close button --}}
            <div class="absolute top-0 left-full flex w-16 justify-center pt-5">
                <button type="button" @click="isSidebarOpen = false"
                        class="-m-2.5 p-2.5 text-gray-600 hover:text-amber-600 focus:outline-none">
                    <span class="sr-only">Close sidebar</span>
                    <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Mobile Sidebar Content --}}
            <div class="flex h-full flex-col gap-y-5 overflow-y-auto bg-white border-r border-gray-200 px-6 pb-4 sidebar-scrollbar shadow-xl">
                {{-- Logo --}}
                <div class="flex h-16 shrink-0 items-center border-b border-gray-100">
                    <div class="relative">
                        <img src="/Auag.jpg" alt="AUAG Jewelry" class="h-10 w-auto rounded-full ring-2 ring-amber-500" />
                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"></div>
                    </div>
                    <span class="ml-3 text-lg font-semibold text-gray-900">
                        AUAG Jewelry
                    </span>
                </div>

                <nav class="flex flex-1 flex-col">
                    <ul class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul class="-mx-2 space-y-1">
                                {{-- Dashboard --}}
                                <li>
                                    <a href="{{ route('admin.dashboard') }}"
                                       class="group flex items-center gap-x-3 rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.dashboard')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}">
                                        <div class="{{ request()->routeIs('admin.dashboard') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                            <svg class="size-5 {{ request()->routeIs('admin.dashboard') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                                 fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                            </svg>
                                        </div>
                                        Dashboard
                                    </a>
                                </li>

                                {{-- Users (dropdown) --}}
                                <li x-data="{ openUsers: {{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*') ? 'true' : 'false' }} }">
                                    <button type="button"
                                        @click="openUsers = !openUsers"
                                        class="w-full group flex items-center justify-between gap-x-3 rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                            {{ (request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*'))
                                                ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700'
                                                : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}">
                                        <span class="flex items-center gap-x-3">
                                            <div class="{{ (request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*')) ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                                <svg class="size-5 {{ (request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*')) ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0ZM4.5 9.75A2.25 2.25 0 0 0 2.25 12v.75A2.25 2.25 0 0 0 4.5 15h4.75a4.5 4.5 0 0 1 4.5 4.5v.378a9.337 9.337 0 0 0 4.121-.952A4.125 4.125 0 0 0 18 13.5V12a2.25 2.25 0 0 0-2.25-2.25h-11Z" />
                                                </svg>
                                            </div>
                                            <span>Users</span>
                                        </span>
                                        <svg :class="openUsers ? 'rotate-180 text-amber-600' : 'text-gray-400'"
                                             class="size-4 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06L10.53 12.53a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" />
                                        </svg>
                                    </button>

                                    <div x-show="openUsers" x-collapse class="mt-1 space-y-1 pl-14 border-l border-gray-200 ml-3">
                                        <a href="{{ route('admin.customers.index') }}"
                                           class="block rounded-md px-3 py-2 text-sm font-medium transition-colors
                                            {{ request()->routeIs('admin.customers.*') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-50 hover:text-amber-600' }}">
                                            Customers
                                        </a>
                                        <a href="{{ route('admin.staff.index') }}"
                                           class="block rounded-md px-3 py-2 text-sm font-medium transition-colors
                                            {{ request()->routeIs('admin.staff.*') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-50 hover:text-amber-600' }}">
                                            Staff
                                        </a>
                                    </div>
                                </li>

                                {{-- Products --}}
                                <li>
                                    <a href="{{ route('admin.products.index') }}"
                                       class="group flex items-center gap-x-3 rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.products.*')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}">
                                        <div class="{{ request()->routeIs('admin.products.*') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                            <svg class="size-5 {{ request()->routeIs('admin.products.*') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                                 fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                            </svg>
                                        </div>
                                        Products
                                    </a>
                                </li>

                                {{-- Transactions --}}
                                <li>
                                    <a href="{{ route('admin.transactions.index') }}"
                                       class="group flex items-center gap-x-3 rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.transactions.*')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}">
                                        <div class="{{ request()->routeIs('admin.transactions.*') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                            <svg class="size-5 {{ request()->routeIs('admin.transactions.*') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                                 fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M8 3h8M5 11h14v10H5z" />
                                            </svg>
                                        </div>
                                        Transactions
                                    </a>
                                </li>

                                {{-- Pawn --}}
                                <li>
                                    <a href="{{ route('admin.pawn.index') }}"
                                       class="group flex items-center gap-x-3 rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.pawn.*')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}">
                                        <div class="{{ request()->routeIs('admin.pawn.*') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                            <svg class="size-5 {{ request()->routeIs('admin.pawn.*') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                                 fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 9v12h16V9l-8-6z" />
                                            </svg>
                                        </div>
                                        Pawn
                                    </a>
                                </li>

                                {{-- Repairs --}}
                                <li>
                                    <a href="{{ route('admin.repairs.index') }}"
                                       class="group flex items-center gap-x-3 rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.repairs.*')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}">
                                        <div class="{{ request()->routeIs('admin.repairs.*') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                            <svg class="size-5 {{ request()->routeIs('admin.repairs.*') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                                 fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 21h4l11-11a2.828 2.828 0 0 0-4-4L4 17v4z" />
                                            </svg>
                                        </div>
                                        Repairs
                                    </a>
                                </li>

                                {{-- Analytics --}}
                                <li>
                                    <a href="{{ route('admin.analytics') }}"
                                       class="group flex items-center gap-x-3 rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.analytics')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}">
                                        <div class="{{ request()->routeIs('admin.analytics') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                            <svg class="size-5 {{ request()->routeIs('admin.analytics') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                                 fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M6 16v-6M12 19V5M18 19v-9" />
                                            </svg>
                                        </div>
                                        Analytics
                                    </a>
                                </li>

                                {{-- Forecast --}}
                                <li>
                                    <a href="{{ route('admin.forecast') }}"
                                       class="group flex items-center gap-x-3 rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.forecast')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}">
                                        <div class="{{ request()->routeIs('admin.forecast') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                            <svg class="size-5 {{ request()->routeIs('admin.forecast') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                                 fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M6 16v-6M12 19V5M18 19v-9" />
                                            </svg>
                                        </div>
                                        Forecast
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- Logout --}}
                        <li class="mt-auto border-t border-gray-100 pt-4">
                            <button type="button"
                                    @click="openLogoutModal()"
                                    class="group flex items-center gap-x-3 rounded-lg p-3 text-sm font-semibold text-gray-700 hover:bg-red-50 hover:text-red-600 w-full transition-colors">
                                <div class="bg-gray-100 group-hover:bg-red-100 p-2 rounded-lg smooth-transition">
                                    <svg class="size-5 text-gray-500 group-hover:text-red-600"
                                         fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3-3m0 0 3 3m-3-3v12" />
                                    </svg>
                                </div>
                                Logout
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    {{-- Static sidebar for desktop --}}
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:flex-col bg-white border-r border-gray-200 sidebar-transition shadow-lg"
         :class="isDesktopSidebarCollapsed ? 'lg:w-20' : 'lg:w-72'">
        <div class="flex grow flex-col gap-y-5 overflow-y-auto px-4 pb-4 sidebar-scrollbar">
            {{-- Logo --}}
            <div class="flex h-16 shrink-0 items-center border-b border-gray-100 mb-2"
                 :class="isDesktopSidebarCollapsed ? 'justify-center' : 'justify-start'">
                <div class="relative">
                    <img src="/Auag.jpg" alt="AUAG Jewelry" class="h-9 w-auto rounded-full ring-2 ring-amber-500" />
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"></div>
                </div>
                <span x-show="!isDesktopSidebarCollapsed"
                      x-transition:enter="transition ease-out duration-300"
                      x-transition:enter-start="opacity-0 translate-x-3"
                      x-transition:enter-end="opacity-100 translate-x-0"
                      class="ml-3 text-lg font-semibold text-gray-900">
                    AUAG Jewelry
                </span>
            </div>

            <nav class="flex flex-1 flex-col">
                <ul class="flex flex-1 flex-col gap-y-7">
                    <li>
                        <ul class="-mx-2 space-y-1">
                            {{-- Dashboard --}}
                            <li>
                                <a href="{{ route('admin.dashboard') }}"
                                   @mouseenter="if (isDesktopSidebarCollapsed) {
                                       activeTooltip = 'Dashboard';
                                       tooltipText = 'Dashboard';
                                       const rect = $el.getBoundingClientRect();
                                       tooltipPosition = { x: rect.right + 8, y: rect.top + rect.height / 2 };
                                   }"
                                   @mouseleave="if (isDesktopSidebarCollapsed) { activeTooltip = null; }"
                                   class="group flex items-center rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.dashboard')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}"
                                   :class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start gap-x-3'">
                                    <div class="{{ request()->routeIs('admin.dashboard') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                        <svg class="size-5 {{ request()->routeIs('admin.dashboard') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                             fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                        </svg>
                                    </div>
                                    <span x-show="!isDesktopSidebarCollapsed" class="truncate sidebar-transition">
                                        Dashboard
                                    </span>
                                </a>
                            </li>

                            {{-- Users (dropdown) --}}
                            <li x-data="{ openUsers: {{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*') ? 'true' : 'false' }} }">
                                <button type="button"
                                    @mouseenter="if (isDesktopSidebarCollapsed) {
                                         activeTooltip = 'Users';
                                         tooltipText = 'Users';
                                         const rect = $el.getBoundingClientRect();
                                         tooltipPosition = { x: rect.right + 8, y: rect.top + rect.height / 2 };
                                     }"
                                    @mouseleave="if (isDesktopSidebarCollapsed) { activeTooltip = null; }"
                                    @click="openUsers = !openUsers"
                                    class="w-full group flex items-center rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ (request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*'))
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}"
                                    :class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start gap-x-3'">
                                    <div class="{{ (request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*')) ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                        <svg class="size-5 {{ (request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*')) ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                             fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0ZM4.5 9.75A2.25 2.25 0 0 0 2.25 12v.75A2.25 2.25 0 0 0 4.5 15h4.75a4.5 4.5 0 0 1 4.5 4.5v.378a9.337 9.337 0 0 0 4.121-.952A4.125 4.125 0 0 0 18 13.5V12a2.25 2.25 0 0 0-2.25-2.25h-11Z" />
                                        </svg>
                                    </div>

                                    <span x-show="!isDesktopSidebarCollapsed" class="flex-1 truncate sidebar-transition text-left">
                                        Users
                                    </span>
                                    <svg x-show="!isDesktopSidebarCollapsed"
                                         :class="openUsers ? 'rotate-180 text-amber-600' : 'text-gray-400'"
                                         class="size-4 transition-transform shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06L10.53 12.53a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" />
                                    </svg>
                                </button>

                                {{-- Submenu --}}
                                <div x-show="openUsers && !isDesktopSidebarCollapsed"
                                     x-collapse
                                     class="mt-1 space-y-1 pl-14 border-l border-gray-200 ml-3">
                                    <a href="{{ route('admin.customers.index') }}"
                                       class="block rounded-md px-3 py-2 text-sm font-medium transition-colors
                                            {{ request()->routeIs('admin.customers.*') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-50 hover:text-amber-600' }}">
                                        Customers
                                    </a>
                                    <a href="{{ route('admin.staff.index') }}"
                                       class="block rounded-md px-3 py-2 text-sm font-medium transition-colors
                                            {{ request()->routeIs('admin.staff.*') ? 'bg-amber-50 text-amber-700' : 'text-gray-600 hover:bg-gray-50 hover:text-amber-600' }}">
                                        Staff
                                    </a>
                                </div>
                            </li>

                            {{-- Products --}}
                            <li>
                                <a href="{{ route('admin.products.index') }}"
                                   @mouseenter="if (isDesktopSidebarCollapsed) {
                                       activeTooltip = 'Products';
                                       tooltipText = 'Products';
                                       const rect = $el.getBoundingClientRect();
                                       tooltipPosition = { x: rect.right + 8, y: rect.top + rect.height / 2 };
                                   }"
                                   @mouseleave="if (isDesktopSidebarCollapsed) { activeTooltip = null; }"
                                   class="group flex items-center rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.products.*')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}"
                                   :class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start gap-x-3'">
                                    <div class="{{ request()->routeIs('admin.products.*') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                        <svg class="size-5 {{ request()->routeIs('admin.products.*') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                             fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                        </svg>
                                    </div>
                                    <span x-show="!isDesktopSidebarCollapsed" class="truncate sidebar-transition">
                                        Products
                                    </span>
                                </a>
                            </li>

                            {{-- Transactions --}}
                            <li>
                                <a href="{{ route('admin.transactions.index') }}"
                                   @mouseenter="if (isDesktopSidebarCollapsed) {
                                       activeTooltip = 'Transactions';
                                       tooltipText = 'Transactions';
                                       const rect = $el.getBoundingClientRect();
                                       tooltipPosition = { x: rect.right + 8, y: rect.top + rect.height / 2 };
                                   }"
                                   @mouseleave="if (isDesktopSidebarCollapsed) { activeTooltip = null; }"
                                   class="group flex items-center rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.transactions.*')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}"
                                   :class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start gap-x-3'">
                                    <div class="{{ request()->routeIs('admin.transactions.*') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                        <svg class="size-5 {{ request()->routeIs('admin.transactions.*') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                             fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M8 3h8M5 11h14v10H5z" />
                                        </svg>
                                    </div>
                                    <span x-show="!isDesktopSidebarCollapsed" class="truncate sidebar-transition">
                                        Transactions
                                    </span>
                                </a>
                            </li>

                            {{-- Pawn --}}
                            <li>
                                <a href="{{ route('admin.pawn.index') }}"
                                   @mouseenter="if (isDesktopSidebarCollapsed) {
                                       activeTooltip = 'Pawn';
                                       tooltipText = 'Pawn';
                                       const rect = $el.getBoundingClientRect();
                                       tooltipPosition = { x: rect.right + 8, y: rect.top + rect.height / 2 };
                                   }"
                                   @mouseleave="if (isDesktopSidebarCollapsed) { activeTooltip = null; }"
                                   class="group flex items-center rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.pawn.*')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}"
                                   :class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start gap-x-3'">
                                    <div class="{{ request()->routeIs('admin.pawn.*') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                        <svg class="size-5 {{ request()->routeIs('admin.pawn.*') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                             fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 9v12h16V9l-8-6z" />
                                        </svg>
                                    </div>
                                    <span x-show="!isDesktopSidebarCollapsed" class="truncate sidebar-transition">
                                        Pawn
                                    </span>
                                </a>
                            </li>

                            {{-- Repairs --}}
                            <li>
                                <a href="{{ route('admin.repairs.index') }}"
                                   @mouseenter="if (isDesktopSidebarCollapsed) {
                                       activeTooltip = 'Repairs';
                                       tooltipText = 'Repairs';
                                       const rect = $el.getBoundingClientRect();
                                       tooltipPosition = { x: rect.right + 8, y: rect.top + rect.height / 2 };
                                   }"
                                   @mouseleave="if (isDesktopSidebarCollapsed) { activeTooltip = null; }"
                                   class="group flex items-center rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.repairs.*')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}"
                                   :class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start gap-x-3'">
                                    <div class="{{ request()->routeIs('admin.repairs.*') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                        <svg class="size-5 {{ request()->routeIs('admin.repairs.*') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                             fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 21h4l11-11a2.828 2.828 0 0 0-4-4L4 17v4z" />
                                        </svg>
                                    </div>
                                    <span x-show="!isDesktopSidebarCollapsed" class="truncate sidebar-transition">
                                        Repairs
                                    </span>
                                </a>
                            </li>

                            {{-- Analytics --}}
                            <li>
                                <a href="{{ route('admin.analytics') }}"
                                   @mouseenter="if (isDesktopSidebarCollapsed) {
                                       activeTooltip = 'Analytics';
                                       tooltipText = 'Analytics';
                                       const rect = $el.getBoundingClientRect();
                                       tooltipPosition = { x: rect.right + 8, y: rect.top + rect.height / 2 };
                                   }"
                                   @mouseleave="if (isDesktopSidebarCollapsed) { activeTooltip = null; }"
                                   class="group flex items-center rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.analytics')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}"
                                   :class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start gap-x-3'">
                                    <div class="{{ request()->routeIs('admin.analytics') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                        <svg class="size-5 {{ request()->routeIs('admin.analytics') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                             fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M6 16v-6M12 19V5M18 19v-9" />
                                        </svg>
                                    </div>
                                    <span x-show="!isDesktopSidebarCollapsed" class="truncate sidebar-transition">
                                        Analytics
                                    </span>
                                </a>
                            </li>

                            {{-- Analytics --}}
                            <li>
                                <a href="{{ route('admin.forecast') }}"
                                   @mouseenter="if (isDesktopSidebarCollapsed) {
                                       activeTooltip = 'Forecast';
                                       tooltipText = 'Forecast';
                                       const rect = $el.getBoundingClientRect();
                                       tooltipPosition = { x: rect.right + 8, y: rect.top + rect.height / 2 };
                                   }"
                                   @mouseleave="if (isDesktopSidebarCollapsed) { activeTooltip = null; }"
                                   class="group flex items-center rounded-lg p-3 text-sm font-semibold transition-all duration-200
                                        {{ request()->routeIs('admin.analytics')
                                            ? 'bg-gradient-to-r from-amber-50 to-yellow-50 text-amber-700 border-l-4 border-amber-500 shadow-sm'
                                            : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }}"
                                   :class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start gap-x-3'">
                                    <div class="{{ request()->routeIs('admin.forecast') ? 'gold-gradient-light' : 'bg-gray-100 group-hover:bg-amber-100' }} p-2 rounded-lg smooth-transition">
                                        <svg class="size-5 {{ request()->routeIs('admin.forecast') ? 'text-amber-600' : 'text-gray-500 group-hover:text-amber-600' }}"
                                             fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M6 16v-6M12 19V5M18 19v-9" />
                                        </svg>
                                    </div>
                                    <span x-show="!isDesktopSidebarCollapsed" class="truncate sidebar-transition">
                                        Forecast
                                    </span>
                                </a>
                            </li>

                        </ul>
                    </li>

                    {{-- Logout --}}
                    <li class="mt-auto border-t border-gray-100 pt-4">
                        <button type="button"
                                @mouseenter="if (isDesktopSidebarCollapsed) {
                                    activeTooltip = 'Logout';
                                    tooltipText = 'Logout';
                                    const rect = $el.getBoundingClientRect();
                                    tooltipPosition = { x: rect.right + 8, y: rect.top + rect.height / 2 };
                                }"
                                @mouseleave="if (isDesktopSidebarCollapsed) { activeTooltip = null; }"
                                @click="openLogoutModal()"
                                class="group flex items-center w-full rounded-lg p-3 text-sm font-semibold text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors"
                                :class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start gap-x-3'">
                            <div class="bg-gray-100 group-hover:bg-red-100 p-2 rounded-lg smooth-transition">
                                <svg class="size-5 text-gray-500 group-hover:text-red-600"
                                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3-3m0 0 3 3m-3-3v12" />
                                </svg>
                            </div>
                            <span x-show="!isDesktopSidebarCollapsed" class="truncate sidebar-transition">
                                Logout
                            </span>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    {{-- Global Tooltip --}}
    <div x-show="isDesktopSidebarCollapsed && activeTooltip"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed sidebar-tooltip rounded-lg bg-gray-800 px-3 py-2 text-sm font-medium text-white shadow-lg ring-1 ring-amber-500/50 z-[60]"
         :style="`left: ${tooltipPosition.x}px; top: ${tooltipPosition.y}px; transform: translateY(-50%);`"
         x-cloak>
        <span x-text="tooltipText"></span>
    </div>

    {{-- LUXURY LOGOUT MODAL --}}
    <div x-cloak x-show="logoutModalOpen"
         class="relative z-[99999]"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">

        <div x-show="logoutModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">

                <div x-show="logoutModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     @click.outside="logoutModalOpen = false"
                     class="relative transform overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 to-gray-800 border border-amber-500/30 text-left shadow-2xl transition-all sm:w-full sm:max-w-md luxury-modal">

                    <div class="px-6 py-8 sm:p-8">
                        <div class="flex flex-col items-center text-center">

                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-500/10 mb-5 border border-amber-500/20 shadow-inner">
                                <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                            </div>

                            <h3 class="text-2xl font-serif tracking-wide text-amber-50 mb-2" id="modal-title">
                                Sign Out
                            </h3>

                            <div class="mt-2">
                                <p class="text-sm text-gray-400">
                                    Are you sure you want to logout?
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800/50 px-6 py-4 flex flex-col sm:flex-row-reverse sm:gap-3 border-t border-white/5">
                        <button type="button"
                                @click="document.getElementById('logout-form').submit()"
                                class="inline-flex w-full justify-center items-center rounded-lg gold-gradient-dark px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/20 hover:shadow-amber-500/40 transition-all hover:scale-[1.02] sm:w-auto">
                            Confirm Sign Out
                        </button>
                        <button type="button"
                                @click="logoutModalOpen = false"
                                class="mt-3 inline-flex w-full justify-center items-center rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm font-semibold text-gray-300 hover:text-white hover:border-gray-500 hover:bg-gray-700/30 transition-colors sm:mt-0 sm:w-auto">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LOGOUT FORM (HIDDEN) --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    {{-- MAIN AREA --}}
    <div class="min-h-screen bg-gray-50 main-content-transition"
         :class="isDesktopSidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'">

        {{-- Top bar --}}
        <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">

            {{-- Mobile sidebar toggle --}}
            <button type="button" @click="isSidebarOpen = true"
                    class="-m-2.5 p-2.5 text-gray-700 hover:text-amber-600 lg:hidden">
                <span class="sr-only">Open sidebar</span>
                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            {{-- Desktop sidebar toggle --}}
            <button type="button" @click="isDesktopSidebarCollapsed = !isDesktopSidebarCollapsed"
                    class="-m-2.5 p-2.5 text-gray-600 hover:text-amber-600 hidden lg:block transition-colors">
                <span class="sr-only">Toggle sidebar</span>
                <svg x-show="isDesktopSidebarCollapsed" class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-show="!isDesktopSidebarCollapsed" class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </button>

            {{-- Separator --}}
            <div class="hidden sm:block h-6 w-px bg-gray-300"></div>

            {{-- Breadcrumb and page info --}}
            <div class="flex-1">
                <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
                <p class="text-sm text-gray-600 mt-0.5 hidden sm:block">Welcome back, {{ auth()->user()->name ?? 'Admin' }}!</p>
            </div>

            {{-- Right side controls --}}
            <div class="flex items-center gap-x-4 lg:gap-x-6 ml-auto">
                {{-- Search --}}
                <div class="relative hidden lg:block">
                    <div class="relative">
                        <input type="search" placeholder="Search..."
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 w-64">
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Notifications --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-amber-600 transition-colors focus:outline-none">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        @auth
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-0 right-0 w-4 h-4 bg-amber-500 rounded-full text-[10px] text-white flex items-center justify-center font-bold shadow-sm">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        @endauth
                    </button>

                    <div x-show="open" @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50 border border-gray-200 card-hover">
                        <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-lg">
                            <h3 class="font-semibold text-gray-900">Notifications</h3>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <form action="/admin/notifications/read-all" method="POST">
                                    @csrf
                                    <button type="submit" class="text-sm text-amber-600 hover:text-amber-700 focus:outline-none font-medium">
                                        Mark all as read
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            @forelse(auth()->user()->notifications->take(10) as $notification)
                                <div class="p-4 border-b hover:bg-gray-50 cursor-pointer
                                {{ $notification->read_at ? 'bg-white' : 'bg-amber-50/50' }}"
                                     onclick="window.location.href='{{ $notification->data['url'] ?? '#' }}'">
                                    <div class="flex justify-between items-start">
                                        <p class="font-semibold text-sm text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                        @if(!$notification->read_at)
                                            <span class="w-2 h-2 bg-amber-500 rounded-full shadow-sm"></span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                            @empty
                                <div class="p-4 text-center text-gray-500">
                                    No notifications
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-300"></div>

                {{-- Profile dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center focus:outline-none group">
                        <span class="sr-only">Open user menu</span>
                        <div class="relative">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                                 alt="Profile" class="size-8 rounded-full ring-2 ring-amber-500 shadow-sm" />
                            <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"></div>
                        </div>
                        <span class="hidden lg:flex lg:items-center ml-3">
                            <span class="text-sm font-semibold text-gray-900 group-hover:text-amber-600 transition-colors">
                                {{ auth()->user()->name ?? 'Admin' }}
                            </span>
                            <svg class="ml-2 size-5 text-gray-400 group-hover:text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                      d="M5.22 8.22a.75.75 0 0 1 1.06.02L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06L10.53 12.53a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" />
                            </svg>
                        </span>
                    </button>

                    {{-- Dropdown menu --}}
                    <div x-show="open"
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-lg bg-white border border-gray-200 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none card-hover">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name ?? 'Admin' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                        <div class="py-1">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-amber-600 transition-colors">
                                Your Profile
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-amber-600 transition-colors">
                                Settings
                            </a>
                        </div>
                        <div class="border-t border-gray-100 py-1">
                            <button @click="openLogoutModal()"
                                    class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors rounded-b-lg">
                                Sign out
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Flash Notification --}}
        @if(session('success') || session('error'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 4000)"
             x-show="show"
             x-transition.opacity.duration.300ms
             aria-live="assertive"
             class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-[9999]">
            <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
                <div class="pointer-events-auto w-full max-w-sm rounded-lg bg-white shadow-lg ring-1 ring-black/5 border border-gray-200 card-hover"
                     x-transition:enter="transform ease-out duration-300"
                     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-x-2 sm:translate-y-0"
                     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="shrink-0">
                                @if(session('success'))
                                <div class="gold-gradient p-2 rounded-lg">
                                    <svg class="size-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                @else
                                <div class="bg-red-100 p-2 rounded-lg">
                                    <svg class="size-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                @endif
                            </div>

                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="text-sm font-semibold text-gray-900">
                                    @if(session('success')) Success! @else Error! @endif
                                </p>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ session('success') ?? session('error') }}
                                </p>
                            </div>

                            <div class="ml-4 flex shrink-0">
                                <button type="button" @click="show = false"
                                        class="inline-flex rounded-md bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-900 focus:outline-none p-1.5 smooth-transition">
                                    <span class="sr-only">Close</span>
                                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Main content area --}}
        <main class="py-8">
            <div class="px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
