<header x-data="{ open: false }"
        class="fixed inset-x-0 top-0 z-50 border-b border-yellow-500/20 bg-gradient-to-r from-gray-900/95 to-gray-800/95 backdrop-blur-lg transition-all duration-300 shadow-xl">

    <div class="max-w-7xl mx-auto px-4">
        <div class="h-20 flex items-center justify-between gap-4">

            {{-- Left: brand --}}
            <div class="flex items-center gap-x-3">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-200 rounded-full blur-sm opacity-30"></div>
                    <img src="{{ asset('storage/Auag.jpg') }}" class="h-12 w-auto relative z-10">
                </div>

                <div class="flex flex-col">
                    <span class="brand-text text-2xl font-bold bg-gradient-to-r from-yellow-400 via-yellow-200 to-yellow-500 bg-clip-text text-transparent">
                        AUAG
                    </span>
                    <span class="text-xs text-yellow-300/80 tracking-widest -mt-1">FINE JEWELRY</span>
                </div>
            </div>

            {{-- Center: Navigation --}}
            <nav class="hidden md:flex items-center justify-center gap-10">
                <a href="{{ url('/') }}"
                   class="nav-underline font-medium text-white/90 hover:text-white {{ request()->is('/') ? 'active text-white' : '' }}">
                    Home
                </a>
                <a href="{{ url('/shop') }}"
                   class="nav-underline font-medium text-white/90 hover:text-white {{ request()->is('shop*') ? 'active text-white' : '' }}">
                    Shop
                </a>
                <a href="{{ url('/pawn') }}"
                   class="nav-underline font-medium text-white/90 hover:text-white {{ request()->is('pawn*') ? 'active text-white' : '' }}">
                    Pawn
                </a>
                <a href="{{ url('/repair') }}"
                   class="nav-underline font-medium text-white/90 hover:text-white {{ request()->is('repair*') ? 'active text-white' : '' }}">
                    Repair
                </a>
                <a href="{{ url('/sell') }}"
                   class="nav-underline font-medium text-white/90 hover:text-white {{ request()->is('sell*') ? 'active text-white' : '' }}">
                    Sell
                </a>
            </nav>

            {{-- Right: Search + Profile (FIXED: Added flex utilities for perfect centering) --}}
            <div class="hidden md:flex items-center gap-4">

                {{-- Search --}}
                <button class="relative group rounded-full border border-yellow-400/30 p-2.5 hover:border-yellow-400 hover:bg-yellow-500/10 transition-all flex items-center justify-center"
                        aria-label="Search">
                    <svg class="w-5 h-5 text-yellow-300 group-hover:text-yellow-400 transition"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7" />
                        <path d="M21 21l-4.3-4.3" />
                    </svg>
                </button>

                {{-- Profile --}}
                <a href="{{ url('/profile') }}"
                   class="relative group rounded-full border border-yellow-400/30 p-2.5 hover:border-yellow-400 hover:bg-yellow-500/10 transition-all flex items-center justify-center"
                   aria-label="Profile">
                    <svg class="w-5 h-5 text-yellow-300 group-hover:text-yellow-400 transition"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="8" r="3.25"></circle>
                        <path d="M5 19a7 7 0 0 1 14 0" stroke-linecap="round"></path>
                    </svg>
                </a>
            </div>

            {{-- Hamburger (mobile) --}}
            <div class="md:hidden">
                <button @click="open = !open"
                        type="button"
                        class="p-3 rounded-xl text-yellow-300 hover:text-yellow-400 hover:bg-yellow-500/10 transition"
                        aria-label="Menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor">
                        <path :class="{'hidden': open, 'block': !open}" class="block"
                              stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'block': open}" class="hidden"
                              stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-cloak x-show="open"
         @click.outside="open=false"
         class="md:hidden border-t border-yellow-500/20 bg-gradient-to-b from-gray-800 to-gray-900 shadow-xl">

        <div class="px-4 py-4 space-y-1">
            <a href="{{ url('/') }}" class="mobile-item">Home</a>
            <a href="{{ url('/shop') }}" class="mobile-item">Shop</a>
            <a href="{{ url('/pawn') }}" class="mobile-item">Pawn</a>
            <a href="{{ url('/repair') }}" class="mobile-item">Repair</a>
            <a href="{{ url('/sell') }}" class="mobile-item">Sell</a>

            <a href="/search"
               class="mobile-item mt-3 flex items-center gap-3">
                <i class="fas fa-search text-yellow-400"></i> Search
            </a>

            <a href="/profile"
               class="mobile-item flex items-center gap-3">
                <i class="fas fa-user text-yellow-400"></i> Profile
            </a>
        </div>
    </div>
</header>

{{-- Mobile item styling --}}
<style>
    .mobile-item {
        @apply block py-3 px-4 rounded-xl font-medium text-white/90 hover:text-white hover:bg-yellow-500/10 transition;
    }

    /* Additional style for active navigation link underline, matching the image */
    .nav-underline {
        position: relative;
    }
    .nav-underline.active::after {
        content: '';
        position: absolute;
        bottom: -6px; /* Position the underline below the text */
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #ffc72c; /* yellow-400/500 */
        border-radius: 1px;
    }
</style>