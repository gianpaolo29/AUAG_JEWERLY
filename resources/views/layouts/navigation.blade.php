<header x-data="{ open: false, scrolled: false }"
        @scroll.window="scrolled = window.pageYOffset > 10"
        :class="{ 'bg-header/95 backdrop-blur-lg border-white/20': scrolled, 'bg-header/50 backdrop-blur-md border-transparent': !scrolled }"
        class="fixed inset-x-0 top-0 z-50 border-b text-white transition-all duration-300 ease-out">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="h-16 md:h-20 flex items-center justify-between">

            {{-- Brand/Logo --}}
            <div class="flex items-center gap-x-3 flex-1 md:flex-none">
                <a href="{{ url('/') }}" class="flex items-center gap-x-3 group">
                    <img src="/Auag.jpg" alt="AUAG Jewelry Logo"
                         class="h-8 w-8 md:h-10 md:w-10 transition-transform group-hover:scale-105">
                    <span class="font-serif text-xl md:text-2xl font-light tracking-wide">AUAG Jewelry</span>
                </a>
            </div>

            {{-- Center Navigation --}}
            <nav class="hidden md:flex items-center justify-center flex-1">
                <div class="flex items-center gap-8 lg:gap-12">
                    @php
                        $navItems = [
                            ['url' => '/', 'label' => 'Home'],
                            ['url' => '/shop', 'label' => 'Shop'],
                            ['url' => '#about', 'label' => 'About'],
                            ['url' => '#contact', 'label' => 'Contact'],
                            ['url' => '/appraisal', 'label' => 'Appraisals'],
                        ];
                    @endphp

                    @foreach($navItems as $item)
                        @if(str_starts_with($item['url'], '#'))
                            <a href="{{ $item['url'] }}"
                               class="relative font-medium text-white/80 hover:text-white transition-colors duration-200
                                      group/nav scroll-smooth"
                               onclick="scrollToSection('{{ $item['url'] }}')">
                                {{ $item['label'] }}
                                <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-white transition-all duration-300 group-hover/nav:w-full"></span>
                            </a>
                        @else
                            <a href="{{ url($item['url']) }}"
                               class="relative font-medium text-white/80 hover:text-white transition-colors duration-200
                                      {{ request()->is(trim($item['url'], '/').'*') ? 'text-white' : '' }}
                                      group/nav">
                                {{ $item['label'] }}
                                <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-white transition-all duration-300 group-hover/nav:w-full {{ request()->is(trim($item['url'], '/').'*') ? 'w-full' : '' }}"></span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center justify-end gap-2 md:gap-3 flex-1 md:flex-none">

                {{-- Search --}}
                <button class="p-2 rounded-full hover:bg-white/10 transition-all duration-200 group"
                        aria-label="Search">
                    <svg class="w-5 h-5 text-white/80 group-hover:text-white group-hover:scale-110 transition-transform"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M21 21l-4.3-4.3"/>
                    </svg>
                </button>

                {{-- Cart --}}
                <button class="p-2 rounded-full hover:bg-white/10 transition-all duration-200 group relative"
                        aria-label="Cart">
                    <svg class="w-5 h-5 text-white/80 group-hover:text-white group-hover:scale-110 transition-transform"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 6h15l-1.5 9h-12z"/>
                        <path d="M6 6 4 3H2"/>
                        <circle cx="9" cy="20" r="1"/>
                        <circle cx="18" cy="20" r="1"/>
                    </svg>
                    {{-- Cart badge --}}
                    <span class="absolute -top-1 -right-1 bg-white text-gray-900 text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
                        0
                    </span>
                </button>

                {{-- Profile --}}
                @auth
                    <div x-data="{ openProfile: false }" class="relative">
                        <button @click="openProfile = !openProfile"
                                class="p-2 rounded-full hover:bg-white/10 transition-all duration-200 group"
                                aria-haspopup="menu" :aria-expanded="openProfile.toString()" aria-label="Profile">
                            <svg class="w-5 h-5 text-white/80 group-hover:text-white group-hover:scale-110 transition-transform"
                                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="3"></circle>
                                <path d="M5 19a7 7 0 0 1 14 0" stroke-linecap="round"></path>
                            </svg>
                        </button>

                        <div x-cloak x-show="openProfile"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             @click.outside="openProfile = false"
                             class="absolute right-0 mt-3 w-56 rounded-xl bg-white/95 backdrop-blur-xl text-gray-900 shadow-2xl ring-1 ring-white/20 z-50 overflow-hidden">
                            <div class="p-2">
                                <div class="px-3 py-2 text-sm font-medium text-gray-700 border-b border-gray-100">
                                    {{ auth()->user()->name }}
                                </div>
                                <a href="{{ route('profile.edit') }}"
                                   class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-white/50 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Profile
                                </a>
                                <div class="border-t my-1 border-gray-100"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="flex items-center w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-white/50 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth

                @guest
                    <a href="{{ route('login') }}"
                       class="p-2 rounded-full hover:bg-white/10 transition-all duration-200 group"
                       aria-label="Sign in">
                        <svg class="w-5 h-5 text-white/80 group-hover:text-white group-hover:scale-110 transition-transform"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="8" r="3"></circle>
                            <path d="M5 19a7 7 0 0 1 14 0" stroke-linecap="round"></path>
                        </svg>
                    </a>
                @endguest
            </div>

            {{-- Mobile Menu Button --}}
            <div class="md:hidden">
                <button @click="open = !open" :aria-expanded="open.toString()" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10 focus:outline-none transition-all duration-200"
                        aria-label="Menu">
                    <svg class="h-6 w-6 transition-transform duration-200" :class="{ 'rotate-90': open }"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    {{-- Mobile Menu --}}
    <div x-cloak x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         @click.outside="open = false"
         class="md:hidden border-t border-white/20 bg-header/95 backdrop-blur-lg">
        <div class="px-4 py-4 space-y-1">
            @foreach($navItems as $item)
                @if(str_starts_with($item['url'], '#'))
                    <a href="{{ $item['url'] }}"
                       @click="open = false"
                       class="block py-3 px-4 rounded-xl font-medium text-white/80 hover:text-white hover:bg-white/5 transition-colors scroll-smooth"
                       onclick="scrollToSection('{{ $item['url'] }}')">
                        {{ $item['label'] }}
                    </a>
                @else
                    <a href="{{ url($item['url']) }}"
                       class="block py-3 px-4 rounded-xl font-medium transition-all duration-200
                              {{ request()->is(trim($item['url'], '/').'*') ? 'bg-white/10 text-white' : 'text-white/80 hover:text-white hover:bg-white/5' }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach

            {{-- Auth Section --}}
            @auth
                <div class="pt-4 mt-4 border-t border-white/20">
                    <div class="px-4 py-2">
                        <div class="text-sm font-medium text-white">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-white/60">{{ auth()->user()->email }}</div>
                    </div>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center py-3 px-4 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition-colors">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="flex items-center w-full text-left py-3 px-4 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition-colors">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            @endauth

            @guest
                <div class="pt-4 mt-4 border-t border-white/20 space-y-1">
                    <a href="{{ route('login') }}"
                       class="block py-3 px-4 rounded-xl font-medium text-white/80 hover:text-white hover:bg-white/5 transition-colors">
                        Sign in
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="block py-3 px-4 rounded-xl font-medium text-white/80 hover:text-white hover:bg-white/5 transition-colors">
                            Create account
                        </a>
                    @endif
                </div>
            @endguest
        </div>
    </div>
</header>

<script>
function scrollToSection(sectionId) {
    const element = document.querySelector(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}
</script>
