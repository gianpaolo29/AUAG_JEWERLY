 <header x-data="{ open: false }"
        class="fixed inset-x-0 top-0 z-50 border-b border-black/10 text-white backdrop-blur-md transition-all bg-header/50">


    <div class="max-w-7xl mx-auto px-4">
        <div class="h-16 flex items-center justify-between gap-4">

            {{-- Left: brand --}}
            <div class="flex items-center gap-x-3">
                <img src="{{ asset('storage/Auag.jpg') }}" alt="Luxe Jewelry Logo" class="h-10 w-auto">
                <span class="sr-only">Home</span>
            </div>

            {{-- Center: links (desktop) --}}
            <div class="hidden md:flex items-center justify-center gap-10">
                <a href="{{ url('/') }}"
                   class="font-medium hover:text-white {{ request()->is('/') ? 'text-white' : 'text-white/90' }}">
                    Home
                </a>
                <a href="{{ url('/shop') }}"
                   class="font-medium hover:text-white {{ request()->is('shop*') ? 'text-white' : 'text-white/90' }}">
                    Shop
                </a>
                <a href="{{ url('/pawn') }}"
                   class="font-medium hover:text-white {{ request()->is('pawn*') ? 'text-white' : 'text-white/90' }}">
                    Pawn
                </a>
                <a href="{{ url('/repair') }}"
                   class="font-medium hover:text-white {{ request()->is('repair*') ? 'text-white' : 'text-white/90' }}">
                    Repair
                </a>
                <a href="{{ url('/sell') }}"
                   class="font-medium hover:text-white {{ request()->is('sell*') ? 'text-white' : 'text-white/90' }}">
                    Sell
                </a>
            </div>

            {{-- Right: controls (desktop) --}}
            <div class="hidden md:flex items-center justify-end gap-3">

                {{-- Search --}}
                <button class="rounded-full border border-white/30 p-2 hover:border-white" aria-label="Search">
                    <svg class="w-5 h-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                    </svg>
                </button>

                {{-- Cart --}}
                <button class="rounded-full border border-white/30 p-2 hover:border-white" aria-label="Cart">
                    <svg class="w-5 h-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 4 3H2"/>
                        <circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>
                    </svg>
                </button>

                {{-- Profile --}}
                @auth
                    <div x-data="{ openProfile:false }" class="relative">
                        <button @click="openProfile = !openProfile"
                                class="rounded-full border border-white/30 p-2 hover:border-white"
                                aria-haspopup="menu" :aria-expanded="openProfile.toString()" aria-label="Profile">
                            <svg class="w-5 h-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="8" r="3.25"></circle>
                                <path d="M5 19a7 7 0 0 1 14 0" stroke-linecap="round"></path>
                            </svg>
                        </button>

                        <div x-cloak x-show="openProfile" @click.outside="openProfile=false"
                             class="absolute right-0 mt-3 w-48 rounded-lg bg-white text-gray-900 shadow-lg ring-1 ring-black/10 z-50">
                            <div class="p-2">
                                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md hover:bg-gray-100">Profile</a>
                                <div class="border-t my-2 border-gray-200"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-3 py-2 rounded-md hover:bg-gray-100">
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth

                @guest
                    <a href="{{ route('login') }}"
                       class="rounded-full border border-white/30 p-2 hover:border-white"
                       aria-label="Sign in">
                        <svg class="w-5 h-5 text-white/90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="8" r="3.25"></circle>
                            <path d="M5 19a7 7 0 0 1 14 0" stroke-linecap="round"></path>
                        </svg>
                    </a>
                @endguest
            </div>

            {{-- Hamburger (mobile) --}}
            <div class="md:hidden">
                <button @click="open = !open" :aria-expanded="open.toString()" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-white/80 hover:text-white hover:bg-white/10 focus:outline-none transition"
                        aria-label="Menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden"
                              stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-cloak x-show="open" @click.outside="open=false" class="md:hidden border-t border-white/10">
        <div class="px-4 py-3 space-y-2">
            <a href="{{ url('/') }}"
               class="block py-2 font-medium {{ request()->is('/') ? 'text-white' : 'text-white/90 hover:text-white' }}">
                Home
            </a>
            <a href="{{ url('/shop') }}" class="block py-2 text-white/90 hover:text-white font-medium">Shop</a>
            <a href="{{ url('/pawn') }}" class="block py-2 text-white/90 hover:text-white font-medium">Pawn</a>
            <a href="{{ url('/repair') }}" class="block py-2 text-white/90 hover:text-white font-medium">Repair</a>
            <a href="{{ url('/sell') }}" class="block py-2 text-white/90 hover:text-white font-medium">Sell</a>

            {{-- Auth section --}}
            @auth
                <div class="pt-3 mt-3 border-t border-white/10">
                    <div class="text-sm font-medium text-white">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-white/70">{{ auth()->user()->email }}</div>

                    <div class="mt-3 space-y-1">
                        <a href="{{ route('profile.edit') }}" class="block py-2 text-white/90 hover:text-white">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left py-2 text-white/90 hover:text-white">Log Out</button>
                        </form>
                    </div>
                </div>
            @endauth

            @guest
                <div class="pt-3 mt-3 border-t border-white/10 space-y-2">
                    <a href="{{ route('login') }}" class="block py-2 font-medium text-white/90 hover:text-white">Sign in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="block py-2 font-medium text-white/90 hover:text-white">Create account</a>
                    @endif
                </div>
            @endguest
        </div>
    </div>
</header> 
