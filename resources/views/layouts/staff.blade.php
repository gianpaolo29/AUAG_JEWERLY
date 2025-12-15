<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'AUAG Jewelry' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('Auag.jpg') }}">

    {{-- Tailwind & App Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased" x-data="{ logoutModalOpen: false }">

    {{-- SUCCESS TOAST NOTIFICATION --}}
    @if (session('success'))
        <div 
            x-data="{ show: true }" 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            x-init="setTimeout(() => show = false, 3500)"
            class="fixed top-4 right-4 z-[9999] bg-green-600 text-white px-5 py-3 rounded-xl shadow-2xl flex items-center space-x-3" 
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>

            <button @click="show = false" class="ml-2 text-white/70 hover:text-white transition">
                ✕
            </button>
        </div>
    @endif

    {{-- HEADER (Now Static - No Scroll Change) --}}
    <header x-data="{ open: false }"
            class="fixed inset-x-0 top-0 z-50 border-b border-white/10 text-white bg-gray-900 shadow-md">

        @php
            $navItems = [
                ['url' => '/staff/dashboard', 'label' => 'Dashboard'],
                ['url' => '/staff/products', 'label' => 'Products'],
                ['url' => '/staff/transactions', 'label' => 'Transactions'],
                ['url' => '/staff/pawns', 'label' => 'Pawn'],
                ['url' => '/staff/repairs', 'label' => 'Repair'],
            ];
        @endphp

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 md:h-20 flex items-center justify-between">

                {{-- LOGO / BRAND --}}
                <div class="flex items-center gap-x-4 flex-1 md:flex-none">
                    <a href="{{ url('/') }}" class="flex items-center gap-x-3 group">
                        <img src="/Auag.jpg" alt="AUAG Jewelry"
                             class="h-9 w-9 md:h-10 md:w-10 rounded-full border border-white/20 transition-transform group-hover:scale-105 shadow-sm">
                        <span class="font-serif text-xl md:text-2xl tracking-wide text-yellow-50 group-hover:text-white transition-colors">
                            AUAG Jewelry
                        </span>
                    </a>
                </div>

                {{-- DESKTOP NAVIGATION --}}
                <nav class="hidden md:flex items-center justify-center flex-1">
                    <div class="flex items-center gap-8 lg:gap-10">
                        @foreach($navItems as $item)
                            @php
                                $isActive = !str_starts_with($item['url'], '#') && request()->is(trim($item['url'], '/').'*');
                            @endphp

                            <a href="{{ $item['url'] }}"
                               @if(str_starts_with($item['url'], '#')) onclick="scrollToSection('{{ $item['url'] }}')" @endif
                               class="relative text-sm font-medium tracking-wide transition-colors duration-300 group/nav
                                      {{ $isActive ? 'text-yellow-400' : 'text-gray-300 hover:text-white' }}">
                                {{ $item['label'] }}
                                <span class="absolute -bottom-1.5 left-0 h-[2px] bg-yellow-400 transition-all duration-300
                                      {{ $isActive ? 'w-full' : 'w-0 group-hover/nav:w-full' }}"></span>
                            </a>
                        @endforeach
                    </div>
                </nav>

                {{-- RIGHT ACTIONS --}}
                <div class="flex items-center justify-end gap-3 flex-1 md:flex-none">

                    {{-- Search Icon --}}
                    <button class="p-2 rounded-full text-gray-300 hover:text-white hover:bg-white/10 transition focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="M21 21l-4.3-4.3"/>
                        </svg>
                    </button>

                    {{-- NOTIFICATIONS --}}
                    <div x-data="{ openNotif: false }" class="relative hidden sm:block">
                        <button @click="openNotif = !openNotif"
                                class="p-2 rounded-full text-gray-300 hover:text-white hover:bg-white/10 transition relative focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M15 17h5l-1.4-1.4A2 2 0 0118 14V11a6 6 0 10-12 0v3a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9" />
                            </svg>

                            @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-1 right-1 bg-red-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center ring-2 ring-gray-900">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>

                        <div x-cloak x-show="openNotif" @click.outside="openNotif = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute right-0 mt-3 w-80 bg-white shadow-xl rounded-xl text-gray-900 overflow-hidden ring-1 ring-black/5 z-50">
                            
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                                <h3 class="text-sm font-semibold text-gray-700">Notifications</h3>
                            </div>

                            <div class="max-h-96 overflow-y-auto">
                                @php $notifications = auth()->user() ? auth()->user()->notifications()->limit(10)->get() : []; @endphp
                                @forelse($notifications as $notification)
                                    <a href="{{ route('notifications.read', $notification->id) }}"
                                       class="block px-4 py-3 border-b border-gray-100 hover:bg-yellow-50 transition
                                              {{ $notification->read_at ? 'text-gray-500' : 'text-gray-900 font-medium bg-blue-50/30' }}">
                                        <p class="text-sm">{{ $notification->data['message'] ?? 'New notification' }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    </a>
                                @empty
                                    <div class="px-4 py-6 text-center text-sm text-gray-500">
                                        No new notifications
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- USER PROFILE --}}
                    @auth
                        <div x-data="{ openProfile: false }" class="relative hidden sm:block">
                            <button @click="openProfile = !openProfile"
                                    class="p-1 rounded-full text-gray-300 hover:text-white hover:bg-white/10 transition focus:outline-none flex items-center gap-2">
                                <div class="h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center text-gray-900 font-bold text-sm">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            </button>

                            <div x-cloak x-show="openProfile" @click.outside="openProfile = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute right-0 mt-3 w-48 bg-white shadow-xl rounded-xl text-gray-900 overflow-hidden ring-1 ring-black/5 z-50">
                                
                                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                                </div>

                                <a href="{{ route('staff.profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-yellow-700">
                                    Profile Settings
                                </a>

                                {{-- UPDATED: text-center here --}}
                                <button @click="logoutModalOpen = true" class="block w-full text-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">
                                    Sign Out
                                </button>
                            </div>
                        </div>
                    @endauth

                    @guest
                        <a href="{{ route('login') }}" class="text-sm font-medium text-white hover:text-yellow-400 transition hidden sm:block">
                            Log in
                        </a>
                    @endguest

                    {{-- MOBILE MENU BUTTON --}}
                    <div class="md:hidden flex items-center">
                        <button @click="open = !open" class="p-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 transition focus:outline-none">
                            <svg class="h-6 w-6" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" stroke-width="2">
                                <path x-show="!open" d="M4 6h16M4 12h16M4 18h16"/>
                                <path x-show="open" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- MOBILE MENU DROPDOWN --}}
        <div x-cloak x-show="open" x-collapse
             @click.outside="open = false"
             class="md:hidden bg-gray-900 border-t border-white/10 shadow-2xl">
            
            <div class="px-4 py-3 space-y-1">
                @foreach($navItems as $item)
                    @php $isActive = !str_starts_with($item['url'], '#') && request()->is(trim($item['url'], '/').'*'); @endphp
                    <a href="{{ $item['url'] }}"
                       @if(str_starts_with($item['url'], '#')) onclick="scrollToSection('{{ $item['url'] }}'); open=false" @endif
                       class="block px-4 py-3 rounded-lg text-base font-medium transition
                              {{ $isActive ? 'bg-yellow-500/10 text-yellow-400' : 'text-gray-300 hover:bg-white/5 hover:text-white' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            @auth
                <div class="border-t border-white/10 pt-4 pb-4">
                    <div class="px-6 flex items-center">
                        <div class="h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center text-gray-900 font-bold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-white">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-400">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="mt-3 px-2 space-y-1">
                        <a href="{{ route('staff.profile.edit') }}" class="block px-4 py-2 rounded-lg text-gray-300 hover:bg-white/5 hover:text-white">
                            Your Profile
                        </a>
                        <button @click="logoutModalOpen = true" class="block w-full text-left px-4 py-2 rounded-lg text-red-400 hover:bg-white/5">
                            Logout
                        </button>
                    </div>
                </div>
            @endauth
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main class="pt-20 min-h-screen">
        {{ $slot }}
    </main>

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
                     class="relative transform overflow-hidden rounded-2xl bg-gray-900 border border-yellow-500/30 text-left shadow-2xl transition-all sm:w-full sm:max-w-md">
                    
                    <div class="px-6 py-8 sm:p-8">
                        <div class="flex flex-col items-center text-center">
                            
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-yellow-500/10 mb-5 border border-yellow-500/20 shadow-inner">
                                <svg class="h-7 w-7 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                            </div>
                            
                            <h3 class="text-2xl font-serif tracking-wide text-yellow-50 mb-2" id="modal-title">
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
                                class="inline-flex w-full justify-center items-center rounded-lg bg-gradient-to-r from-yellow-600 to-yellow-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-yellow-500/20 hover:from-yellow-500 hover:to-yellow-400 transition-all sm:w-auto">
                            Confirm Sign Out
                        </button>
                        <button type="button" 
                                @click="logoutModalOpen = false"
                                class="mt-3 inline-flex w-full justify-center items-center rounded-lg border border-gray-600 bg-transparent px-4 py-2.5 text-sm font-semibold text-gray-300 hover:text-white hover:border-gray-500 transition-colors sm:mt-0 sm:w-auto">
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

    <script>
        function scrollToSection(sectionId) {
            const el = document.querySelector(sectionId);
            if (el) el.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>