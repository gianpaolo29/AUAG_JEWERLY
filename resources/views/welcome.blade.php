<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    .no-scrollbar::-webkit-scrollbar{display:none}
    .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
  </style>
</head>
<body class="bg-page text-ink font-sans">

  @include('layouts.navigation')


  <!-- Hero -->
  <section class="relative h-[88vh] overflow-hidden">
    <img src="{{asset('storage/Model.webp') }}"
      alt="Jewelry on marble" class="absolute inset-0 w-full h-full object-cover brightness-90" />
    <div class="absolute inset-0 bg-black/30"></div>

    <div class="relative z-10 max-w-7xl mx-auto h-full px-4 flex items-center justify-center text-center">
      <div class="max-w-4xl">
        <h1 class="font-serif text-white text-5xl md:text-7xl font-semibold leading-tight">
          Timeless Elegance,<br/> Crafted to Perfection
        </h1>
        <p class="text-white/90 mt-5 text-lg">Discover our exquisite collection of handcrafted jewelry</p>
        <a href="{{ url('/shop') }}" class="inline-flex items-center gap-2 mt-10 rounded-2xl border border-white/80 px-8 py-4 text-lg font-medium text-white hover:bg-white hover:text-header transition">
          Shop Now
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M5 12h14M13 5l7 7-7 7"/></svg>
        </a>
      </div>
    </div>
  </section>

  <!-- Best Sellers (carousel) -->
  <section id="best" class="max-w-7xl mx-auto px-4 py-20">
    <div class="text-center mb-10">
      <h2 class="font-serif text-4xl">Best Sellers</h2>
      <p class="text-black/60 mt-2">Discover our most coveted pieces</p>
    </div>

    <div class="relative">
      <button id="prevBtn"
              class="hidden md:grid absolute left-0 -translate-x-1/2 top-1/2 -translate-y-1/2 place-items-center w-12 h-12 rounded-full bg-black/10 backdrop-blur border border-black/10 hover:bg-black/20">
        <svg class="w-5 h-5 text-black/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M15 19l-7-7 7-7"/></svg>
      </button>
      <button id="nextBtn"
              class="hidden md:grid absolute right-0 translate-x-1/2 top-1/2 -translate-y-1/2 place-items-center w-12 h-12 rounded-full bg-black/10 backdrop-blur border border-black/10 hover:bg-black/20">
        <svg class="w-5 h-5 text-black/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M9 5l7 7-7 7"/></svg>
      </button>

      <div id="track"
           class="flex gap-6 overflow-x-auto no-scrollbar snap-x snap-mandatory scroll-smooth pr-2">
        @php
          $products = [
            ['Eternal Solitaire Ring', '2,850', asset('Storage/Ring.jpg')],
            ['Classic Pearl Necklace', '1,650', asset('Storage/Necklace.jpg')],
            ['Diamond Tennis Bracelet', '3,200', asset('Storage/Bracelet.jpg')],
            ['Drop Earrings', '980', asset('Storage/Earrings.jpg')],
          ];
        @endphp

        @foreach($products as $p)
        <article class="snap-center min-w-[85%] sm:min-w-[48%] lg:min-w-[31%] bg-white rounded-3xl overflow-hidden shadow-xl">
          <img src="{{ $p[2] }}" alt="{{ $p[0] }}" class="w-full aspect-[4/3] object-cover">
          <div class="px-6 py-5">
            <h3 class="font-serif text-xl">{{ $p[0] }}</h3>
            <p class="text-black/60 mt-1">${{ $p[1] }}</p>
          </div>
        </article>
        @endforeach
      </div>
    </div>
  </section>

  <!-- Categories -->
  <section class="max-w-7xl mx-auto px-4 py-16">
    <div class="text-center mb-10">
      <h3 class="font-serif text-4xl">Shop by Category</h3>
      <p class="text-black/60 mt-2">Explore our curated collections</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      @php
        $cats = [
          ['Rings',     asset('Storage/Ring.jpg')],
          ['Necklaces', asset('Storage/Necklace.jpg')],
          ['Earrings',  asset('Storage/Earrings.jpg')],
          ['Bracelets', asset('Storage/Bracelet.jpg')],
        ];
      @endphp
      @foreach($cats as $c)
      <a href="#" class="group relative rounded-3xl overflow-hidden">
        <img src="{{ $c[1] }}" alt="{{ $c[0] }}"
             class="w-full aspect-[4/3] object-cover grayscale group-hover:grayscale-0 transition duration-500 group-hover:scale-[1.03]"/>
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="absolute inset-0 flex items-center justify-center">
          <span class="text-white font-serif text-2xl">{{ $c[0] }}</span>
        </div>
      </a>
      @endforeach
    </div>
  </section>

  
  <section class="relative overflow-hidden">
    <div class="absolute inset-0">
      <img src="https://wpcdn.us-east-1.vip.tn-cloud.net/www.hawaiimagazine.com/content/uploads/2022/11/c/h/thumbnail-paradisecollectionaffordablejewelry-1024x683.jpg"
           alt="" class="w-full h-full object-cover brightness-50">
    </div>
    <div class="relative max-w-7xl mx-auto px-4 py-24 lg:py-28">
      <div class="grid lg:grid-cols-2 gap-8 items-center">
        <div class="text-white">
          <h3 class="font-serif text-5xl">Heritage Collection</h3>
          <p class="text-white/90 mt-4 text-lg">Each piece tells a story of timeless craftsmanship, passed through generations.</p>
          <a href="#" class="inline-flex items-center gap-2 mt-8 rounded-2xl border border-white/80 px-6 py-3 text-base text-white hover:bg-white hover:text-header transition">
            Explore Collection
          </a>
        </div>
        <div class="bg-black rounded-[28px] p-2 shadow-2xl">
          <img src="https://wpcdn.us-east-1.vip.tn-cloud.net/www.hawaiimagazine.com/content/uploads/2022/11/c/h/thumbnail-paradisecollectionaffordablejewelry-1024x683.jpg"
               class="w-full aspect-[4/3] object-cover rounded-[20px]" alt="Featured piece">
        </div>
      </div>
    </div>
  </section>

  
  <section id="about" class="max-w-5xl mx-auto px-4 py-24">
    <h3 class="font-serif text-5xl text-center">Our Story</h3>
    <p class="mt-8 text-lg text-center text-black/70 leading-8">
      Founded in 1952, our legacy began with a simple vision: to create jewelry that captures life's most precious moments.
      Three generations later, we continue to honor traditional craftsmanship while embracing innovative design.
    </p>
    <p class="mt-6 text-lg text-center text-black/70 leading-8">
      Every piece is meticulously handcrafted in our atelier, where master jewelers transform the finest materials into
      heirloom treasures that will be cherished for generations.
    </p>
    <div class="text-center mt-10">
      <a href="#" class="inline-flex items-center gap-2 rounded-xl bg-black text-white px-6 py-3 hover:opacity-90">Learn More</a>
    </div>
  </section>

  
  <section class="bg-[#eceff1] py-16">
    <div class="max-w-3xl mx-auto px-4 text-center">
      <h4 class="font-serif text-4xl">Stay Connected</h4>
      <p class="text-black/60 mt-2">Be the first to discover our latest collections and exclusive offers</p>
      <form class="mt-6 flex gap-3 max-sm:flex-col items-center justify-center"
            onsubmit="event.preventDefault(); alert('Thanks for subscribing!')">
        <input type="email" required placeholder="Enter your email"
          class="w-full max-w-md rounded-2xl border border-black/20 bg-white px-5 py-3 outline-none focus:border-black/60">
        <button class="rounded-2xl bg-black text-white px-6 py-3 hover:opacity-90">Subscribe</button>
      </form>
    </div>
  </section>

  <footer id="footer" class="bg-black text-white">
    <div class="max-w-7xl mx-auto px-4 py-14 grid gap-10 md:grid-cols-4">

      <div>
        <img src="{{ asset('Storage/Auag.jpg') }}" alt="Luxe Jewelry Logo" class="h-10 w-auto mb-4">
        <p class="text-white/70 max-w-sm">
          Crafting timeless elegance since 1952. Every piece tells a story of exceptional artistry and enduring beauty.
        </p>
      </div>

     
      <div>
        <div class="font-serif text-xl mb-3">Collections</div>
        <ul class="space-y-2 text-white/80">
          <li><a href="#" class="hover:text-white">Engagement Rings</a></li>
          <li><a href="#" class="hover:text-white">Wedding Bands</a></li>
          <li><a href="#" class="hover:text-white">Necklaces</a></li>
          <li><a href="#" class="hover:text-white">Earrings</a></li>
        </ul>
      </div>

    
      <div>
        <div class="font-serif text-xl mb-3">Services</div>
        <ul class="space-y-2 text-white/80">
          <li><a href="#" class="hover:text-white">Custom Design</a></li>
          <li><a href="#" class="hover:text-white">Repair & Resize</a></li>
          <li><a href="#" class="hover:text-white">Appraisal</a></li>
          <li><a href="#" class="hover:text-white">Care Guide</a></li>
        </ul>
      </div>

      
      <div>
        <div class="font-serif text-xl mb-3">Connect</div>
        <div class="flex gap-3">
          <a href="#" class="rounded-full border border-white/20 p-2 hover:bg-white hover:text-black transition" aria-label="Facebook">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
              <path d="M13.5 9H15V6h-1.5C11.6 6 11 7.1 11 8.7V10H9v3h2v7h3v-7h2.1l.4-3H14v-1c0-.6.2-1 1-1z"/>
            </svg>
          </a>
          <a href="#" class="rounded-full border border-white/20 p-2 hover:bg-white hover:text-black transition" aria-label="Instagram">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="3.5"/><circle cx="17" cy="7" r="1"/>
            </svg>
          </a>
          <a href="#" class="rounded-full border border-white/20 p-2 hover:bg-white hover:text-black transition" aria-label="Twitter">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
              <path d="M22 5.8c-.7.3-1.5.6-2.3.7.8-.5 1.4-1.3 1.7-2.2-.8.5-1.7.9-2.6 1.1C18 4.6 17 4 15.8 4c-2.2 0-3.9 2.1-3.4 4.3-3.3-.2-6.3-1.7-8.3-4.1-.9 1.6-.4 3.6 1.1 4.7-.6 0-1.2-.2-1.7-.5 0 1.7 1.2 3.3 3.1 3.7-.5.1-1 .2-1.5.1.4 1.4 1.8 2.5 3.5 2.5-1.5 1.2-3.4 1.8-5.3 1.6 1.6 1 3.6 1.5 5.6 1.5 6.8 0 10.7-5.9 10.5-11.1.7-.5 1.3-1.2 1.7-2z"/>
            </svg>
          </a>
        </div>
      </div>
    </div>

    <div class="border-t border-white/10">
      <div class="max-w-7xl mx-auto px-4 py-6 text-sm text-white/70 flex items-center justify-between">
        <p>© 2025 Luxe Jewelry. All rights reserved.</p>
        <div class="flex gap-6">
          <a href="#" class="hover:text-white">Privacy Policy</a>
          <a href="#" class="hover:text-white">Terms of Service</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Vanilla JS for small interactions -->
  <script>
    // Header background on scroll (robust add/remove)
    const header = document.getElementById('siteHeader');
    function updateHeader() {
      if (window.scrollY > 10) {
        header.classList.remove('bg-header/50','bg-header/70');
        header.classList.add('bg-header/90','shadow');
      } else {
        header.classList.remove('bg-header/90','shadow');
        header.classList.add('bg-header/70');
      }
    }
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });

    // Mobile menu
    const menuBtn = document.getElementById('menuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    menuBtn?.addEventListener('click', () => {
      const isOpen = !mobileMenu.classList.contains('hidden');
      mobileMenu.classList.toggle('hidden', isOpen);
      menuBtn.setAttribute('aria-expanded', String(!isOpen));
    });

    // Profile dropdown (click outside)
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');
    const closeProfile = (v) => {
      profileMenu.classList.toggle('hidden', v);
      profileBtn.setAttribute('aria-expanded', String(!v));
    };
    profileBtn?.addEventListener('click', (e) => {
      e.stopPropagation();
      closeProfile(!profileMenu.classList.contains('hidden'));
    });
    document.addEventListener('click', (e) => {
      if (!profileMenu.classList.contains('hidden')) {
        if (!profileMenu.contains(e.target) && e.target !== profileBtn) closeProfile(true);
      }
    });

    // Carousel
    const track = document.getElementById('track');
    const prev = document.getElementById('prevBtn');
    const next = document.getElementById('nextBtn');
    const gapPx = 24; // gap-6
    const cardWidth = () => {
      const card = track?.querySelector('article');
      return card ? card.getBoundingClientRect().width + gapPx : 0;
    };
    prev?.addEventListener('click', () => track.scrollBy({ left: -cardWidth(), behavior: 'smooth' }));
    next?.addEventListener('click', () => track.scrollBy({ left:  cardWidth(), behavior: 'smooth' }));

    // Drag to scroll
    let isDown = false, startX = 0, scrollLeft = 0;
    track?.addEventListener('pointerdown', (e) => {
      isDown = true;
      startX = e.clientX;
      scrollLeft = track.scrollLeft;
      track.setPointerCapture(e.pointerId);
    });
    track?.addEventListener('pointermove', (e) => {
      if (!isDown) return;
      track.scrollLeft = scrollLeft - (e.clientX - startX);
    });
    ['pointerup','pointerleave','pointercancel'].forEach(ev =>
      track?.addEventListener(ev, () => { isDown = false; })
    );
  </script>
</body>
</html>
