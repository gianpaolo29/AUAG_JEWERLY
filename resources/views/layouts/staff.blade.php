<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'AUAG Jewelry' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('Auag.jpg') }}">

    {{-- Assuming you are using Laravel Mix/Vite/etc. to compile Tailwind CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>
<body class="bg-gray-50 text-gray-900">

@if (session('success'))
    <div 
        x-data="{ show: true }" 
        x-show="show"
        x-init="setTimeout(() => show = false, 3500)"
        {{-- CHANGED z-50 to z-[9999] for maximum priority --}}
        class="fixed top-4 right-4 z-[9999] bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center space-x-2" 
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 13l4 4L19 7" />
        </svg>
        <span>{{ session('success') }}</span>

        <button @click="show = false" class="ml-2 text-white/80 hover:text-white">
            ✕
        </button>
    </div>
@endif

<header x-data="{ open: false, scrolled: false }"
        @scroll.window="scrolled = window.pageYOffset > 10"
        {{-- FIX: Replaced custom 'bg-header' with 'bg-gray-900' for a sleek dark theme --}}
        :class="{
            'bg-gray-900/95 backdrop-blur-lg border-white/20': scrolled,
            'bg-gray-900/50 backdrop-blur-md border-transparent': !scrolled
        }"
        class="fixed inset-x-0 top-0 z-50 border-b text-white transition-all duration-300 ease-out">

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

            {{-- BRAND --}}
            <div class="flex items-center gap-x-3 flex-1 md:flex-none">
                <a href="{{ url('/') }}" class="flex items-center gap-x-3 group">
                    {{-- Consider styling the logo better, maybe changing the image source if available --}}
                    <img src="/Auag.jpg" alt="AUAG Jewelry Logo"
                         class="h-8 w-8 md:h-10 md:w-10 rounded-full transition-transform group-hover:scale-105">
                    <span class="font-serif text-xl md:text-2xl font-light tracking-wide">AUAG Jewelry</span>
                </a>
            </div>

            {{-- NAV DESKTOP --}}
            <nav class="hidden md:flex items-center justify-center flex-1">
                <div class="flex items-center gap-8 lg:gap-12">
                    @foreach($navItems as $item)
                        @php
                            $isActive = !str_starts_with($item['url'], '#') &&
                                         request()->is(trim($item['url'], '/').'*');
                        @endphp

                        {{-- Hash links --}}
                        @if(str_starts_with($item['url'], '#'))
                            <a href="{{ $item['url'] }}"
                               onclick="scrollToSection('{{ $item['url'] }}')"
                               class="relative font-medium text-white/80 hover:text-white group/nav transition">
                                {{ $item['label'] }}
                                {{-- FIX: Made the underline slightly thicker for better visibility --}}
                                <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-yellow-400 transition-all duration-300 group-hover/nav:w-full"></span>
                            </a>
                        @else
                            {{-- Normal links --}}
                            <a href="{{ url($item['url']) }}"
                               class="relative font-medium transition group/nav
                                        {{-- FIX: Use yellow-400 for active state to match jewelry theme --}}
                                        {{ $isActive ? 'text-yellow-400' : 'text-white/80 hover:text-white' }}">
                                {{ $item['label'] }}
                                <span class="absolute -bottom-1 left-0 h-0.5 bg-yellow-400 transition-all duration-300
                                                {{ $isActive ? 'w-full' : 'w-0 group-hover/nav:w-full' }}"></span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </nav>

            {{-- RIGHT ACTIONS --}}
            <div class="flex items-center justify-end gap-2 md:gap-3 flex-1 md:flex-none">

                {{-- Search --}}
                {{-- FIX: Used ring-offset-gray-900 for a better focus ring on dark background --}}
                <button class="p-2 rounded-full hover:bg-white/10 transition focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 focus:ring-offset-gray-900 group">
                    <svg class="w-5 h-5 text-white/80 group-hover:text-white"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M21 21l-4.3-4.3"/>
                    </svg>
                </button>

                {{-- NOTIFICATIONS --}}
                <div x-data="{ openNotif: false }" class="relative hidden sm:block">
                    <button @click="openNotif = !openNotif"
                            class="p-2 rounded-full hover:bg-white/10 transition relative focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 focus:ring-offset-gray-900">
                        
                        <svg class="w-5 h-5 text-white/80 group-hover:text-white"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M15 17h5l-1.4-1.4A2 2 0 0118 14V11a6 6 0 10-12 0v3a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9" />
                        </svg>

                        {{-- UNREAD COUNT BADGE --}}
                        @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                            <span class="absolute -top-1 -right-1 bg-yellow-400 text-gray-900 text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </button>

                    {{-- DROPDOWN --}}
                    <div x-cloak x-show="openNotif"
                        @click.outside="openNotif = false"
                        x-transition
                        class="absolute right-0 mt-3 w-80 bg-white shadow-xl rounded-xl text-gray-900 overflow-hidden z-50">

                        <div class="px-4 py-2 font-semibold bg-gray-50 border-b">
                            Notifications
                        </div>

                        @php
                            $notifications = auth()->user()->notifications()->limit(10)->get();
                        @endphp

                        @forelse($notifications as $notification)
                            <a href="{{ route('notifications.read', $notification->id) }}"
                            class="block px-4 py-3 border-b hover:bg-yellow-50 {{ $notification->read_at ? 'text-gray-500' : 'text-gray-900 font-medium' }}">
                            
                            {{ $notification->data['message'] ?? 'New notification' }}

                            <div class="text-xs text-gray-500">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                            </a>
                        @empty
                            <div class="px-4 py-4 text-center text-gray-500">
                                No notifications
                            </div>
                        @endforelse
                    </div>
                </div>



                {{-- PROFILE DESKTOP --}}
                @auth
                    <div x-data="{ openProfile: false }" class="relative hidden sm:block">
                        <button @click="openProfile = !openProfile"
                                {{-- FIX: Added focus state for accessibility --}}
                                class="p-2 rounded-full hover:bg-white/10 transition focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 focus:ring-offset-gray-900 group">
                            <svg class="w-5 h-5 text-white/80 group-hover:text-white"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="3"/>
                                <path d="M5 19a7 7 0 0 1 14 0"/>
                            </svg>
                        </button>

                        {{-- Dropdown --}}
                        <div x-cloak x-show="openProfile"
                             @click.outside="openProfile = false"
                             x-transition
                             class="absolute right-0 mt-3 w-56 bg-white shadow-xl rounded-xl text-gray-900 overflow-hidden">

                            <div class="px-3 py-2 text-sm font-medium border-b bg-gray-50">
                                {{ auth()->user()->name }}
                            </div>

                            <a href="{{ route('staff-profile.edit') }}"
                               class="block px-3 py-2 text-sm hover:bg-yellow-50 hover:text-yellow-800">
                                Profile
                            </a>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="block w-full text-left px-3 py-2 text-sm hover:bg-yellow-50 hover:text-yellow-800">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth

                @guest
                    <a href="{{ route('login') }}"
                       class="p-2 rounded-full hover:bg-white/10 transition group hidden sm:block focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 focus:ring-offset-gray-900">
                        <svg class="w-5 h-5 text-white/80 group-hover:text-white"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="8" r="3"/>
                            <path d="M5 19a7 7 0 0 1 14 0"/>
                        </svg>
                    </a>
                @endguest

                {{-- MOBILE BUTTON --}}
                <div class="md:hidden">
                    {{-- FIX: Added focus state for accessibility and switched to yellow-400 ring --}}
                    <button @click="open = !open" class="p-2 rounded-lg hover:bg-white/10 transition focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 focus:ring-offset-gray-900">
                        <svg class="h-6 w-6"
                             :class="{ 'rotate-90': open }"
                             fill="none" stroke="currentColor" stroke-width="2">
                            <path x-show="!open" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="open" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- MOBILE MENU --}}
    <div x-cloak x-show="open"
         x-transition
         @click.outside="open = false"
         {{-- FIX: Use bg-gray-900 for consistency --}}
         class="md:hidden border-t border-white/20 bg-gray-900/95 backdrop-blur-lg">

        <div class="px-4 py-4 space-y-1">

            {{-- Mobile nav --}}
            @foreach($navItems as $item)
                @if(str_starts_with($item['url'], '#'))
                    <a href="{{ $item['url'] }}"
                       onclick="scrollToSection('{{ $item['url'] }}'); open=false"
                       {{-- FIX: Improved hover/active states for dark background --}}
                       class="block py-3 px-4 rounded-xl text-white/80 hover:text-yellow-400 hover:bg-white/5">
                        {{ $item['label'] }}
                    </a>
                @else
                    <a href="{{ url($item['url']) }}"
                       class="block py-3 px-4 rounded-xl
                                {{-- FIX: Use yellow-400 for active text --}}
                                {{ request()->is(trim($item['url'], '/').'*') 
                                    ? 'bg-white/10 text-yellow-400' 
                                    : 'text-white/80 hover:text-yellow-400 hover:bg-white/5' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach

            {{-- MOBILE AUTH --}}
            @auth
                <div class="pt-4 mt-4 border-t border-white/20">
                    <div class="px-4 py-2">
                        <div class="text-sm font-medium text-white">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-white/60">{{ auth()->user()->email }}</div>
                    </div>

                    <a href="{{ route('profile.edit') }}"
                       class="block py-3 px-4 text-white/80 hover:text-yellow-400 hover:bg-white/5">
                        Profile
                    </a>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="block w-full text-left py-3 px-4 text-white/80 hover:text-yellow-400 hover:bg-white/5">
                            Log Out
                        </button>
                    </form>
                </div>
            @endauth

            @guest
                <div class="pt-4 mt-4 border-t border-white/20 space-y-1">
                    <a href="{{ route('login') }}"
                       class="block py-3 px-4 rounded-xl text-white/80 hover:text-yellow-400 hover:bg-white/5">
                        Sign in
                    </a>

                    @if(Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="block py-3 px-4 rounded-xl text-white/80 hover:text-yellow-400 hover:bg-white/5">
                            Create account
                        </a>
                    @endif
                </div>
            @endguest

        </div>
    </div>
</header>
{{-- PAGE CONTENT SLOT --}}
<main class="pt-20">
    {{ $slot }}
</main>

<script>
function scrollToSection(sectionId) {
    const el = document.querySelector(sectionId);
    if (el) el.scrollIntoView({ behavior: 'smooth' });
}
</script>

</body>
</html>