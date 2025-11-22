@props(['title' => 'Admin Dashboard'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title }} | AUAG Admin</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    {{-- NOTE: Alpine.js Collapse is needed for the Users dropdown smooth animation --}}
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/flowbite@2.3.0/dist/flowbite.min.js"></script>
    @stack('scripts')
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
</head>
<body class="h-full antialiased" 
    x-data="{ 
        isSidebarOpen: false, 
        isDesktopSidebarCollapsed: localStorage.getItem('isDesktopSidebarCollapsed') === 'true' 
    }"
    x-init="$watch('isDesktopSidebarCollapsed', value => localStorage.setItem('isDesktopSidebarCollapsed', value))"
>
    {{-- Mobile sidebar (off-canvas) --}}
    <div x-show="isSidebarOpen" class="relative z-50 lg:hidden" x-cloak>
        {{-- Backdrop --}}
        <div x-show="isSidebarOpen"
             x-transition:enter="transition-opacity duration-300 ease-linear"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-300 ease-linear"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="isSidebarOpen = false"
             class="fixed inset-0 bg-gray-900/80">
        </div>

        {{-- Sidebar panel --}}
        <div x-show="isSidebarOpen"
             x-transition:enter="transition duration-300 ease-in-out transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition duration-300 ease-in-out transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 z-50 flex w-full max-w-xs flex-1">

            {{-- Close button --}}
            <div class="absolute top-0 left-full flex w-16 justify-center pt-5">
                <button type="button" @click="isSidebarOpen = false" class="-m-2.5 p-2.5 focus:outline-none">
                    <span class="sr-only">Close sidebar</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                         aria-hidden="true" class="size-6 text-white">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            {{-- Mobile Sidebar Content - UPDATED TO MATCH DESKTOP COLOR --}}
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-gradient-to-b from-gray-900 to-gray-950 border-r border-gray-800 px-6 pb-4">
                <div class="relative flex h-16 shrink-0 items-center">
                    <img src="{{ asset('storage/Auag.jpg') }}" alt="AUAG Jewelry" class="h-8 w-auto rounded-md" />
                    <span class="ml-3 text-sm font-semibold tracking-[0.2em] text-gray-100 uppercase">
                        Admin
                    </span>
                </div>

                <nav class="relative flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                {{-- Dashboard --}}
                                <li>
                                    <a href="{{ route('admin.dashboard') }}"
                                       class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold
                                        {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="1.5" aria-hidden="true"
                                             class="size-6 shrink-0
                                            {{ request()->routeIs('admin.dashboard') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                            <path
                                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Dashboard
                                    </a>
                                </li>

                                {{-- Users (dropdown) --}}
                                <li x-data="{ openUsers: {{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*') ? 'true' : 'false' }} }">
                                    <button type="button"
                                        @click="openUsers = !openUsers"
                                        class="w-full group flex items-center justify-between gap-x-3 rounded-md p-2 text-sm/6 font-semibold
                                            {{ (request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*')) ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <span class="flex items-center gap-x-3">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5" aria-hidden="true"
                                                class="size-6 shrink-0
                                                    {{ (request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*')) ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                                <path
                                                    d="M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0ZM4.5 9.75A2.25 2.25 0 0 0 2.25 12v.75A2.25 2.25 0 0 0 4.5 15h4.75a4.5 4.5 0 0 1 4.5 4.5v.378a9.337 9.337 0 0 0 4.121-.952A4.125 4.125 0 0 0 18 13.5V12a2.25 2.25 0 0 0-2.25-2.25h-11Z"
                                                    stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <span>Users</span>
                                        </span>

                                        <svg x-bind:class="openUsers ? 'rotate-180 text-indigo-400' : 'text-gray-400'"
                                            class="size-4 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06L10.53 12.53a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" />
                                        </svg>
                                    </button>

                                    <div x-show="openUsers"
                                        x-collapse
                                        class="mt-1 space-y-1 pl-9">
                                        {{-- UPDATED: Same size as parent --}}
                                        <a href="{{ route('admin.customers.index') }}"
                                           class="block rounded-md px-2 py-1.5 text-sm/6 font-semibold text-left
                                            {{ request()->routeIs('admin.customers.*') ? 'bg-indigo-900/30 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            Customers
                                        </a>
                                        <a href="{{ route('admin.staff.index') }}"
                                           class="block rounded-md px-2 py-1.5 text-sm/6 font-semibold text-left
                                            {{ request()->routeIs('admin.staff.*') ? 'bg-indigo-900/30 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                            Staff
                                        </a>
                                    </div>
                                </li>

                                {{-- Products --}}
                                <li>
                                    <a href="{{ route('admin.products.index') }}"
                                       class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold
                                        {{ request()->routeIs('admin.products.*') ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="1.5" aria-hidden="true"
                                             class="size-6 shrink-0
                                                {{ request()->routeIs('admin.products.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                            <path
                                                d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Products
                                    </a>
                                </li>

                                {{-- Transactions --}}
                                <li>
                                    <a href="{{ route('admin.transactions.index') }}"
                                       class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold
                                        {{ request()->routeIs('admin.transactions.*') ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="1.5" aria-hidden="true"
                                             class="size-6 shrink-0
                                                {{ request()->routeIs('admin.transactions.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                            <path d="M3 7h18M8 3h8M5 11h14v10H5z" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Transactions
                                    </a>
                                </li>

                                {{-- Pawn --}}
                                <li>
                                    <a href="{{ route('admin.pawn.index') }}"
                                       class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold
                                        {{ request()->routeIs('admin.pawn.*') ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="1.5" aria-hidden="true"
                                             class="size-6 shrink-0
                                                {{ request()->routeIs('admin.pawn.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                            <path d="M12 3 4 9v12h16V9l-8-6z" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Pawn
                                    </a>
                                </li>

                                {{-- Repairs --}}
                                <li>
                                    <a href="{{ route('admin.repairs.index') }}"
                                       class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold
                                        {{ request()->routeIs('admin.repairs.*') ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="1.5" aria-hidden="true"
                                             class="size-6 shrink-0
                                                {{ request()->routeIs('admin.repairs.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                            <path d="M4 21h4l11-11a2.828 2.828 0 0 0-4-4L4 17v4z" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Repairs
                                    </a>
                                </li>

                                {{-- Analytics --}}
                                <li>
                                    <a href=""
                                       class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold
                                        {{ request()->routeIs('admin.analytics.*') ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="1.5" aria-hidden="true"
                                             class="size-6 shrink-0
                                                {{ request()->routeIs('admin.analytics.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                            <path d="M4 19h16M6 16v-6M12 19V5M18 19v-9" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Analytics
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    {{-- Static sidebar for desktop (collapsible) - FIXED ALIGNMENT AND SCROLL --}}
    <div
        class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:flex-col bg-gradient-to-b from-gray-900 to-gray-950 transition-all duration-300 ease-in-out overflow-hidden"
        x-bind:class="isDesktopSidebarCollapsed ? 'lg:w-20' : 'lg:w-72'">
        <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-800 bg-gray-950/95 px-4 pb-4">
            {{-- Logo - FIXED ALIGNMENT --}}
            <div class="flex h-14 shrink-0 items-center" 
                 x-bind:class="isDesktopSidebarCollapsed ? 'justify-center' : 'justify-start'">
                <img src="{{ asset('storage/Auag.jpg') }}" alt="AUAG Jewelry" class="h-8 w-auto rounded-md" />
                <span x-show="!isDesktopSidebarCollapsed"
                      x-transition:enter="transition ease-out duration-300"
                      x-transition:enter-start="opacity-0 translate-x-3"
                      x-transition:enter-end="opacity-100 translate-x-0"
                      x-transition:leave="transition ease-in duration-100"
                      x-transition:leave-start="opacity-100 translate-x-0"
                      x-transition:leave-end="opacity-0 translate-x-3"
                      class="ml-3 text-sm font-semibold tracking-[0.2em] text-gray-100 uppercase">
                    Admin
                </span>
            </div>

            <nav class="flex flex-1 flex-col">
                <ul role="list" class="flex flex-1 flex-col gap-y-7">
                    <li>
                        <ul role="list" class="-mx-2 space-y-1">
                            {{-- Dashboard --}}
                            <li x-data="{ showTooltip: false }"
                                class="relative">
                                <a href="{{ route('admin.dashboard') }}"
                                   @mouseenter="showTooltip = true"
                                   @mouseleave="showTooltip = false"
                                   class="group flex items-center rounded-md py-2 text-sm/6 font-semibold
                                        {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                                   x-bind:class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start px-3 gap-x-3'">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.5" aria-hidden="true"
                                         class="size-6 shrink-0
                                            {{ request()->routeIs('admin.dashboard') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                        <path
                                            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span x-show="!isDesktopSidebarCollapsed"
                                          class="truncate transition-opacity duration-300 ease-in-out">
                                        Dashboard
                                    </span>
                                </a>
                                
                                {{-- FIXED: Tooltip with proper positioning --}}
                                <div x-show="isDesktopSidebarCollapsed && showTooltip"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white shadow-lg ring-1 ring-black/5"
                                     style="pointer-events: none;"
                                     x-cloak>
                                    Dashboard
                                    <div class="absolute -left-1 top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
                                </div>
                            </li>

                            {{-- Users (dropdown) --}}
                            <li x-data="{ 
                                    showTooltip: false,
                                    openUsers: {{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*') ? 'true' : 'false' }} 
                                }"
                                class="relative">
                                
                                <button type="button"
                                    @mouseenter="showTooltip = true"
                                    @mouseleave="showTooltip = false"
                                    @click="openUsers = !openUsers"
                                    class="w-full group flex items-center rounded-md py-2 text-sm/6 font-semibold
                                        {{ (request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*')) ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                                    x-bind:class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start px-3 gap-x-3'">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.5" aria-hidden="true"
                                         class="size-6 shrink-0
                                            {{ (request()->routeIs('admin.customers.*') || request()->routeIs('admin.staff.*')) ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                        <path
                                            d="M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0ZM4.5 9.75A2.25 2.25 0 0 0 2.25 12v.75A2.25 2.25 0 0 0 4.5 15h4.75a4.5 4.5 0 0 1 4.5 4.5v.378a9.337 9.337 0 0 0 4.121-.952A4.125 4.125 0 0 0 18 13.5V12a2.25 2.25 0 0 0-2.25-2.25h-11Z"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    <span x-show="!isDesktopSidebarCollapsed"
                                          class="flex-1 truncate transition-opacity duration-300 ease-in-out text-left">
                                        Users
                                    </span>
                                    <svg x-show="!isDesktopSidebarCollapsed"
                                         x-bind:class="openUsers ? 'rotate-180 text-indigo-400' : 'text-gray-400'"
                                         class="size-4 transition-transform shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06L10.53 12.53a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" />
                                    </svg>
                                </button>

                                {{-- FIXED: Tooltip with proper positioning --}}
                                <div x-show="isDesktopSidebarCollapsed && showTooltip"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white shadow-lg ring-1 ring-black/5"
                                     style="pointer-events: none;"
                                     x-cloak>
                                    Users
                                    <div class="absolute -left-1 top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
                                </div>

                                {{-- Submenu when expanded - UPDATED: Same size as parent --}}
                                <div x-show="openUsers && !isDesktopSidebarCollapsed"
                                     x-collapse
                                     class="mt-1 space-y-1 pl-10">
                                    <a href="{{ route('admin.customers.index') }}"
                                       class="block rounded-md px-2 py-1.5 text-sm/6 font-semibold text-left
                                            {{ request()->routeIs('admin.customers.*') ? 'bg-indigo-900/30 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        Customers
                                    </a>
                                    <a href="{{ route('admin.staff.index') }}"
                                       class="block rounded-md px-2 py-1.5 text-sm/6 font-semibold text-left
                                            {{ request()->routeIs('admin.staff.*') ? 'bg-indigo-900/30 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                        Staff
                                    </a>
                                </div>
                            </li>

                            {{-- Products --}}
                            <li x-data="{ showTooltip: false }"
                                class="relative">
                                <a href="{{ route('admin.products.index') }}"
                                   @mouseenter="showTooltip = true"
                                   @mouseleave="showTooltip = false"
                                   class="group flex items-center rounded-md py-2 text-sm/6 font-semibold
                                        {{ request()->routeIs('admin.products.*') ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                                   x-bind:class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start px-3 gap-x-3'">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.5" aria-hidden="true"
                                         class="size-6 shrink-0
                                            {{ request()->routeIs('admin.products.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                        <path
                                            d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span x-show="!isDesktopSidebarCollapsed"
                                          class="truncate transition-opacity duration-300 ease-in-out">
                                        Products
                                    </span>
                                </a>
                                
                                {{-- FIXED: Tooltip with proper positioning --}}
                                <div x-show="isDesktopSidebarCollapsed && showTooltip"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white shadow-lg ring-1 ring-black/5"
                                     style="pointer-events: none;"
                                     x-cloak>
                                    Products
                                    <div class="absolute -left-1 top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
                                </div>
                            </li>

                            {{-- Transactions --}}
                            <li x-data="{ showTooltip: false }"
                                class="relative">
                                <a href="{{ route('admin.transactions.index') }}"
                                   @mouseenter="showTooltip = true"
                                   @mouseleave="showTooltip = false"
                                   class="group flex items-center rounded-md py-2 text-sm/6 font-semibold
                                        {{ request()->routeIs('admin.transactions.*') ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                                   x-bind:class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start px-3 gap-x-3'">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.5" aria-hidden="true"
                                         class="size-6 shrink-0
                                            {{ request()->routeIs('admin.transactions.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                        <path d="M3 7h18M8 3h8M5 11h14v10H5z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span x-show="!isDesktopSidebarCollapsed"
                                          class="truncate transition-opacity duration-300 ease-in-out">
                                        Transactions
                                    </span>
                                </a>
                                
                                {{-- FIXED: Tooltip with proper positioning --}}
                                <div x-show="isDesktopSidebarCollapsed && showTooltip"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white shadow-lg ring-1 ring-black/5"
                                     style="pointer-events: none;"
                                     x-cloak>
                                    Transactions
                                    <div class="absolute -left-1 top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
                                </div>
                            </li>

                            {{-- Pawn --}}
                            <li x-data="{ showTooltip: false }"
                                class="relative">
                                <a href="{{ route('admin.pawn.index') }}"
                                   @mouseenter="showTooltip = true"
                                   @mouseleave="showTooltip = false"
                                   class="group flex items-center rounded-md py-2 text-sm/6 font-semibold
                                        {{ request()->routeIs('admin.pawn.*') ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                                   x-bind:class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start px-3 gap-x-3'">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.5" aria-hidden="true"
                                         class="size-6 shrink-0
                                            {{ request()->routeIs('admin.pawn.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                        <path d="M12 3 4 9v12h16V9l-8-6z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span x-show="!isDesktopSidebarCollapsed"
                                          class="truncate transition-opacity duration-300 ease-in-out">
                                        Pawn
                                    </span>
                                </a>
                                
                                {{-- FIXED: Tooltip with proper positioning --}}
                                <div x-show="isDesktopSidebarCollapsed && showTooltip"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white shadow-lg ring-1 ring-black/5"
                                     style="pointer-events: none;"
                                     x-cloak>
                                    Pawn
                                    <div class="absolute -left-1 top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
                                </div>
                            </li>

                            {{-- Repairs --}}
                            <li x-data="{ showTooltip: false }"
                                class="relative">
                                <a href="{{ route('admin.repairs.index') }}"
                                   @mouseenter="showTooltip = true"
                                   @mouseleave="showTooltip = false"
                                   class="group flex items-center rounded-md py-2 text-sm/6 font-semibold
                                        {{ request()->routeIs('admin.repairs.*') ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                                   x-bind:class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start px-3 gap-x-3'">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.5" aria-hidden="true"
                                         class="size-6 shrink-0
                                            {{ request()->routeIs('admin.repairs.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                        <path d="M4 21h4l11-11a2.828 2.828 0 0 0-4-4L4 17v4z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span x-show="!isDesktopSidebarCollapsed"
                                          class="truncate transition-opacity duration-300 ease-in-out">
                                        Repairs
                                    </span>
                                </a>
                                
                                {{-- FIXED: Tooltip with proper positioning --}}
                                <div x-show="isDesktopSidebarCollapsed && showTooltip"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white shadow-lg ring-1 ring-black/5"
                                     style="pointer-events: none;"
                                     x-cloak>
                                    Repairs
                                    <div class="absolute -left-1 top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
                                </div>
                            </li>

                            {{-- Analytics --}}
                            <li x-data="{ showTooltip: false }"
                                class="relative">
                                <a href=""
                                   @mouseenter="showTooltip = true"
                                   @mouseleave="showTooltip = false"
                                   class="group flex items-center rounded-md py-2 text-sm/6 font-semibold
                                        {{ request()->routeIs('admin.analytics.*') ? 'bg-indigo-900/50 text-indigo-300' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                                   x-bind:class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start px-3 gap-x-3'">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.5" aria-hidden="true"
                                         class="size-6 shrink-0
                                            {{ request()->routeIs('admin.analytics.*') ? 'text-indigo-400' : 'text-gray-400 group-hover:text-indigo-300' }}">
                                        <path d="M4 19h16M6 16v-6M12 19V5M18 19v-9" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span x-show="!isDesktopSidebarCollapsed"
                                          class="truncate transition-opacity duration-300 ease-in-out">
                                        Analytics
                                    </span>
                                </a>
                                
                                {{-- FIXED: Tooltip with proper positioning --}}
                                <div x-show="isDesktopSidebarCollapsed && showTooltip"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white shadow-lg ring-1 ring-black/5"
                                     style="pointer-events: none;"
                                     x-cloak>
                                    Analytics
                                    <div class="absolute -left-1 top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
                                </div>
                            </li>
                        </ul>
                    </li>

                    {{-- Logout --}}
                    <li class="mt-auto relative" x-data="{ showTooltip: false }">
                        <form method="POST" action="{{ route('logout') }}" class="-mx-2">
                            <button type="submit"
                                    @mouseenter="showTooltip = true"
                                    @mouseleave="showTooltip = false"
                                    class="group flex items-center w-full rounded-md py-2 text-sm/6 font-semibold text-red-300 hover:bg-red-950/40 hover:text-red-200"
                                    x-bind:class="isDesktopSidebarCollapsed ? 'justify-center px-2' : 'justify-start px-3 gap-x-3'">
                                @csrf
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="1.5" aria-hidden="true"
                                     class="size-6 shrink-0 text-red-400 group-hover:text-red-300">
                                    <path
                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3-3m0 0 3 3m-3-3v12"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span x-show="!isDesktopSidebarCollapsed"
                                      class="truncate transition-opacity duration-300 ease-in-out">
                                    Logout
                                </span>
                            </button>
                        </form>
                        
                        {{-- FIXED: Tooltip with proper positioning --}}
                        <div x-show="isDesktopSidebarCollapsed && showTooltip"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-full bottom-1/2 z-50 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md bg-red-800 px-3 py-2 text-sm font-medium text-white shadow-lg ring-1 ring-black/5"
                             style="pointer-events: none;"
                             x-cloak>
                            Logout
                            <div class="absolute -left-1 top-1/2 -translate-y-1/2 border-4 border-transparent border-r-red-800"></div>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    {{-- Rest of the code remains exactly the same... --}}

    {{-- MAIN AREA --}}
    <div
        class="min-h-screen bg-gray-50 transition-all duration-300 ease-in-out"
        x-bind:class="isDesktopSidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'">
        
        {{-- Top bar --}}
        <div
            class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white/95 px-4 shadow-xs backdrop-blur-sm sm:gap-x-6 sm:px-6 lg:px-8">
            
            {{-- Mobile sidebar toggle --}}
            <button type="button" @click="isSidebarOpen = true"
                    class="-m-2.5 p-2.5 text-gray-700 hover:text-gray-900 lg:hidden">
                <span class="sr-only">Open sidebar</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"
                     class="size-6">
                    <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                          stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>

            {{-- Desktop sidebar toggle --}}
            <button type="button" @click="isDesktopSidebarCollapsed = !isDesktopSidebarCollapsed"
                    class="-m-2.5 p-2.5 text-gray-700 hover:text-gray-900 hidden lg:block">
                <span class="sr-only">Toggle desktop sidebar</span>
                <svg x-show="isDesktopSidebarCollapsed" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.5" aria-hidden="true" class="size-6">
                    <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                          stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <svg x-show="!isDesktopSidebarCollapsed" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.5" aria-hidden="true" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </button>

            <div aria-hidden="true" class="hidden sm:block h-6 w-px bg-gray-200"></div>

            <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                <div class="flex items-center gap-x-4 lg:gap-x-6 ml-auto">
                    {{-- Notification Icon --}}
                    <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
                        <span class="sr-only">View notifications</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                             aria-hidden="true" class="size-6">
                            <path
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div aria-hidden="true" class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200"></div>

                    {{-- Profile dropdown --}}
                    <el-dropdown class="relative">
                        <button class="relative flex items-center">
                            <span class="absolute -inset-1.5"></span>
                            <span class="sr-only">Open user menu</span>
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                                 alt="" class="size-8 rounded-full bg-gray-50 outline -outline-offset-1 outline-black/5" />
                            <span class="hidden lg:flex lg:items-center">
                                <span aria-hidden="true" class="ml-4 text-sm/6 font-semibold text-gray-900">
                                    {{ auth()->user()->name ?? 'Admin' }}
                                </span>
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                                     class="ml-2 size-5 text-gray-400">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                          d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06L10.53 12.53a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 0-1.06Z" />
                                </svg>
                            </span>
                        </button>
                        <el-menu anchor="bottom end" popover
                                 class="w-40 origin-top-right rounded-md bg-white py-2 shadow-lg outline-1 outline-gray-900/5 transition data-closed:scale-95 data-closed:opacity-0">
                            <span class="block px-3 py-1 text-sm/6 text-gray-900">
                                {{ auth()->user()->email ?? '' }}
                            </span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="block w-full px-3 py-1 text-left text-sm/6 text-gray-900 hover:bg-gray-50">
                                    Sign out
                                </button>
                            </form>
                        </el-menu>
                    </el-dropdown>
                </div>
            </div>
        </div>

        {{-- Flash Notification --}}
        @if(session('success') || session('error'))
        <div 
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 4000)"
            x-show="show"
            x-transition.opacity.duration.300ms
            aria-live="assertive"
            class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-[9999]"
        >
            <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
                <div 
                    class="pointer-events-auto w-full max-w-sm rounded-lg bg-white shadow-lg outline-1 outline-black/5 transition duration-300 ease-out transform"
                    x-transition:enter="transform ease-out duration-300"
                    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-x-2 sm:translate-y-0"
                    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="shrink-0">
                                @if(session('success'))
                                <svg class="size-6 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                @else
                                <svg class="size-6 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M12 9v3m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                @endif
                            </div>

                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="text-sm font-medium text-gray-900">
                                    @if(session('success'))
                                        Success
                                    @else
                                        Error
                                    @endif
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ session('success') ?? session('error') }}
                                </p>
                            </div>

                            <div class="ml-4 flex shrink-0">
                                <button type="button" @click="show = false"
                                        class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none">
                                    <span class="sr-only">Close notification</span>
                                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-5">
                                        <path
                                            d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
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