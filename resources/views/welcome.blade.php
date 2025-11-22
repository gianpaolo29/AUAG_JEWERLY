<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <title>AUAG Jewelry</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


  <style>
    .no-scrollbar::-webkit-scrollbar{display:none}
    .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
    .gradient-text {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .hero-gradient {
      background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 100%);
    }
  </style>
</head>
<body class="bg-page text-ink font-sans antialiased">

    @if (session('login_success'))
    <script>
        Swal.fire({
            title: 'Welcome to AUAG!',
            text: @json(session('login_success')),
            icon: 'success',
            confirmButtonColor: '#000',
        });
    </script>
    @endif

  @include('layouts.navigation')

  <!-- Modern Hero Section - FIXED: Text moved down -->
  <section class="relative min-h-screen overflow-hidden">
    <!-- Background with subtle overlay -->
    <div class="absolute inset-0">
      <img src="/Model.webp"
           alt="Jewelry on marble"
           class="w-full h-full object-cover object-center" />
      <div class="absolute inset-0 hero-gradient"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto h-full px-6 flex items-end justify-center text-center pb-32">
      <div class="max-w-4xl space-y-8">
        <!-- Animated badge -->
        <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md rounded-full px-6 py-3 border border-white/30">
          <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
          <span class="text-white/90 text-sm font-medium">Since 2024 - Crafting Timeless Elegance</span>
        </div>

        <!-- Main heading with gradient - MOVED DOWN -->
        <div class="max-w-4xl pt-20">
        <h1 class="font-serif text-white text-5xl md:text-7xl font-semibold leading-tight">
          Timeless Elegance,<br/> Crafted to Perfection
        </h1>
        <p class="text-white/90 mt-5 text-lg">Discover our exquisite collection of handcrafted jewelry</p>
        </div>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mt-12">
          <a href="{{ url('/shop') }}"
             class="group relative overflow-hidden bg-white text-gray-900 px-8 py-4 rounded-2xl font-medium text-lg hover:scale-105 transition-all duration-300 shadow-2xl">
            <span class="relative z-10">Explore Collection</span>
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-cyan-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          </a>

          <a href="#about"
             onclick="scrollToSection('#about')"
             class="group border-2 border-white/50 text-white px-8 py-4 rounded-2xl font-medium text-lg hover:bg-white hover:text-gray-900 transition-all duration-300 backdrop-blur-sm">
            <span class="flex items-center gap-2">
              Our Story
              <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </span>
          </a>
        </div>
      </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
      <div class="animate-bounce">
        <svg class="w-6 h-6 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
        </svg>
      </div>
    </div>
  </section>

  <!-- Featured Collections -->
  <section class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="font-serif text-5xl md:text-6xl text-gray-900 mb-4">Curated Collections</h2>
        <p class="text-gray-600 text-xl max-w-2xl mx-auto">Each piece tells a story of exceptional craftsmanship and timeless beauty</p>
      </div>

      <div class="grid md:grid-cols-3 gap-8">
        <!-- Collection 1 -->
        <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-50 to-orange-100 p-8 hover:shadow-2xl transition-all duration-500">
          <div class="relative z-10">
            <h3 class="font-serif text-2xl text-gray-900 mb-4">Signature Collection</h3>
            <p class="text-gray-600 mb-6">Our most iconic designs that define modern elegance</p>
            <a href="#" class="inline-flex items-center text-gray-900 font-medium hover:gap-3 transition-all duration-300">
              Discover
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </a>
          </div>
          <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-gradient-to-r from-rose-200 to-orange-200 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
        </div>

        <!-- Collection 2 -->
        <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-50 to-cyan-100 p-8 hover:shadow-2xl transition-all duration-500">
          <div class="relative z-10">
            <h3 class="font-serif text-2xl text-gray-900 mb-4">Heritage Pieces</h3>
            <p class="text-gray-600 mb-6">Timeless designs passed through generations</p>
            <a href="#" class="inline-flex items-center text-gray-900 font-medium hover:gap-3 transition-all duration-300">
              Explore
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </a>
          </div>
          <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-gradient-to-r from-blue-200 to-cyan-200 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
        </div>

        <!-- Collection 3 -->
        <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-50 to-teal-100 p-8 hover:shadow-2xl transition-all duration-500">
          <div class="relative z-10">
            <h3 class="font-serif text-2xl text-gray-900 mb-4">Modern Classics</h3>
            <p class="text-gray-600 mb-6">Contemporary designs with timeless appeal</p>
            <a href="#" class="inline-flex items-center text-gray-900 font-medium hover:gap-3 transition-all duration-300">
              View All
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </a>
          </div>
          <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-gradient-to-r from-emerald-200 to-teal-200 rounded-full opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- Best Sellers (carousel) - FIXED: Removed Add to Cart button -->
  <section class="max-w-7xl mx-auto px-4 py-20">
    <div class="text-center mb-10">
      <h2 class="font-serif text-4xl">Best Sellers</h2>
      <p class="text-black/60 mt-2">Discover our most coveted pieces</p>
    </div>

    <div class="relative">
      <button id="prevBtn"
              class="hidden md:grid absolute left-0 -translate-x-1/2 top-1/2 -translate-y-1/2 place-items-center w-12 h-12 rounded-full bg-black/10 backdrop-blur border border-black/10 hover:bg-black/20 transition-all duration-300">
        <svg class="w-5 h-5 text-black/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M15 19l-7-7 7-7"/></svg>
      </button>
      <button id="nextBtn"
              class="hidden md:grid absolute right-0 translate-x-1/2 top-1/2 -translate-y-1/2 place-items-center w-12 h-12 rounded-full bg-black/10 backdrop-blur border border-black/10 hover:bg-black/20 transition-all duration-300">
        <svg class="w-5 h-5 text-black/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M9 5l7 7-7 7"/></svg>
      </button>

      <div id="track"
           class="flex gap-6 overflow-x-auto no-scrollbar snap-x snap-mandatory scroll-smooth pr-2">
        @foreach($bestSellers as $product)
          <article class="snap-center min-w-[85%] sm:min-w-[48%] lg:min-w-[31%] bg-white rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300">
            <img
              src="{{ $product->image_url ?? asset('images/placeholder-product.png') }}"
              alt="{{ $product->name }}"
              class="w-full aspect-[4/3] object-cover"
            >
            <div class="px-6 py-5">
              <h3 class="font-serif text-xl">{{ $product->name }}</h3>
              <p class="text-black/60 mt-1">
                ₱{{ number_format((float) $product->price, 2) }}
              </p>
            </div>
          </article>
        @endforeach
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
      <div class="grid lg:grid-cols-2 gap-16 items-center">
        <div class="space-y-8">
          <div>
            <span class="text-emerald-600 font-medium tracking-wider">OUR LEGACY</span>
            <h2 class="font-serif text-5xl text-gray-900 mt-4">Crafting Stories Since 2024</h2>
          </div>

          <div class="space-y-6">
            <p class="text-gray-600 text-lg leading-relaxed">
              For over year, AUAG Jewelry has been synonymous with exceptional craftsmanship and timeless elegance.
              Our journey began with a simple vision: to create pieces that capture life's most precious moments.
            </p>

            <p class="text-gray-600 text-lg leading-relaxed">
              Today, we continue to honor traditional techniques while embracing innovative design,
              ensuring each piece becomes a cherished heirloom.
            </p>
          </div>

          <div class="grid grid-cols-3 gap-8 pt-8 border-t border-gray-200">
            <div>
              <div class="text-3xl font-light text-gray-900 mb-2">1+</div>
              <div class="text-gray-600 text-sm">Years of Excellence</div>
            </div>
            <div>
              <div class="text-3xl font-light text-gray-900 mb-2">10k+</div>
              <div class="text-gray-600 text-sm">Happy Clients</div>
            </div>
            <div>
              <div class="text-3xl font-light text-gray-900 mb-2">100%</div>
              <div class="text-gray-600 text-sm">Authentic</div>
            </div>
          </div>
        </div>

        <div class="relative">
          <div class="grid grid-cols-2 gap-6">
            <div class="space-y-6">
              <div class="aspect-square rounded-3xl overflow-hidden bg-white shadow-lg">
                <img src="https://images.unsplash.com/photo-1605100804763-247f67b3557e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"
                     alt="Craftsmanship" class="w-full h-full object-cover hover:scale-110 transition-transform duration-700">
              </div>
              <div class="aspect-square rounded-3xl overflow-hidden bg-white shadow-lg">
                <img src="https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"
                     alt="Materials" class="w-full h-full object-cover hover:scale-110 transition-transform duration-700">
              </div>
            </div>
            <div class="space-y-6 pt-12">
              <div class="aspect-square rounded-3xl overflow-hidden bg-white shadow-lg">
                <img src="https://images.unsplash.com/photo-1588444650700-6c7f0c89d36b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"
                     alt="Design" class="w-full h-full object-cover hover:scale-110 transition-transform duration-700">
              </div>
              <div class="aspect-square rounded-3xl overflow-hidden bg-gradient-to-br from-emerald-500 to-cyan-500 flex items-center justify-center shadow-lg">
                <span class="text-white font-serif text-xl text-center px-8">Exceptional Quality Since 2024</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
      <div class="text-center mb-16">
        <h2 class="font-serif text-5xl text-gray-900 mb-4">Get In Touch</h2>
        <p class="text-gray-600 text-xl">We'd love to hear from you. Let's start a conversation.</p>
      </div>

      <div class="grid lg:grid-cols-2 gap-16">
        <!-- Contact Information -->
        <div class="space-y-8">
          <div>
            <h3 class="font-serif text-2xl text-gray-900 mb-6">Visit Our Store</h3>
            <div class="space-y-4">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                </div>
                <div>
                  <div class="font-medium text-gray-900">Located At</div>
                  <div class="text-gray-600">Rizal Tuy, Batangas</div>
                </div>
              </div>

              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                </div>
                <div>
                  <div class="font-medium text-gray-900">Call Us</div>
                  <div class="text-gray-600">+63 945-406-0982</div>
                </div>
              </div>

              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <div class="font-medium text-gray-900">Email Us</div>
                  <div class="text-gray-600">auagjewerlyaccessories@gmail.com</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Store Hours -->
          <div>
            <h3 class="font-serif text-2xl text-gray-900 mb-6">Store Hours</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Monday - Sunday</span>
                <span class="font-medium text-gray-900">8:00 AM - 5:00 PM</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="bg-gradient-to-br from-gray-50 to-white rounded-3xl p-8 shadow-xl">
          <form class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                <input type="text" id="name" name="name"
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white focus:border-gray-400 focus:ring-2 focus:ring-gray-100 transition-all duration-300 outline-none">
              </div>
              <div>
                <label for="lastname" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                <input type="text" id="lastname" name="lastname"
                       class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white focus:border-gray-400 focus:ring-2 focus:ring-gray-100 transition-all duration-300 outline-none">
              </div>
            </div>

            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
              <input type="email" id="email" name="email"
                     class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white focus:border-gray-400 focus:ring-2 focus:ring-gray-100 transition-all duration-300 outline-none">
            </div>

            <div>
              <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
              <textarea id="message" name="message" rows="5"
                        class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-white focus:border-gray-400 focus:ring-2 focus:ring-gray-100 transition-all duration-300 outline-none resize-none"></textarea>
            </div>

            <button type="submit"
                    class="w-full bg-gradient-to-r from-gray-900 to-gray-700 text-white py-4 rounded-2xl font-medium hover:shadow-xl transition-all duration-300 hover:scale-105">
              Send Message
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Newsletter Section -->
  <section class="py-20 bg-gradient-to-br from-rose-50 to-orange-100">
    <div class="max-w-4xl mx-auto px-4 text-center">
      <div class="bg-white/50 backdrop-blur-sm rounded-3xl p-12 shadow-2xl">
        <h3 class="font-serif text-4xl text-gray-900 mb-4">Stay in the Loop</h3>
        <p class="text-gray-600 text-lg mb-8">Be the first to discover new collections, exclusive offers, and styling inspiration.</p>

        <form class="flex gap-4 max-sm:flex-col items-center justify-center"
              onsubmit="event.preventDefault(); showNewsletterSuccess()">
          <input type="email" required placeholder="Enter your email address"
                 class="flex-1 max-w-md px-6 py-4 rounded-2xl border border-gray-200 bg-white focus:border-gray-400 focus:ring-2 focus:ring-gray-100 transition-all duration-300 outline-none">
          <button type="submit"
                  class="bg-gradient-to-r from-gray-900 to-gray-700 text-white px-8 py-4 rounded-2xl font-medium hover:shadow-xl transition-all duration-300 hover:scale-105">
            Subscribe
          </button>
        </form>

        <p class="text-gray-500 text-sm mt-4">By subscribing, you agree to our Privacy Policy</p>
      </div>
    </div>
  </section>

  <!-- Modern Footer -->
  <footer class="bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 py-16 grid gap-10 md:grid-cols-4">
      <!-- Brand -->
      <div class="space-y-4">
        <div class="flex items-center gap-3">
          <img src="/Auag.jpg" alt="AUAG Jewelry Logo" class="h-10 w-10 rounded-lg">
          <span class="font-serif text-2xl font-light">AUAG Jewelry</span>
        </div>
        <p class="text-white/70 text-sm leading-relaxed max-w-sm">
          Crafting timeless elegance since 2024. Every piece tells a story of exceptional artistry and enduring beauty.
        </p>
        <div class="flex gap-3">
          <a href="https://www.facebook.com/profile.php?id=61561631548526&rdid=vsaLMkw7iM31rpIw&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F17pzDZSZAG%2F" class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center hover:bg-white hover:text-gray-900 transition-all duration-300">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M13.5 9H15V6h-1.5C11.6 6 11 7.1 11 8.7V10H9v3h2v7h3v-7h2.1l.4-3H14v-1c0-.6.2-1 1-1z"/>
            </svg>
          </a>
        </div>
      </div>

      <!-- Collections -->
      <div>
        <div class="font-serif text-xl mb-6">Collections</div>
        <ul class="space-y-3 text-white/70">
          <li><a href="#" class="hover:text-white transition-colors duration-200">Engagement Rings</a></li>
          <li><a href="#" class="hover:text-white transition-colors duration-200">Bracelets</a></li>
          <li><a href="#" class="hover:text-white transition-colors duration-200">Necklaces</a></li>
          <li><a href="#" class="hover:text-white transition-colors duration-200">Earrings</a></li>
        </ul>
      </div>

      <!-- Services -->
      <div>
        <div class="font-serif text-xl mb-6">Services</div>
        <ul class="space-y-3 text-white/70">
          <li><a href="#" class="hover:text-white transition-colors duration-200">Custom Design</a></li>
          <li><a href="#" class="hover:text-white transition-colors duration-200">Repair & Resize</a></li>
          <li><a href="#" class="hover:text-white transition-colors duration-200">Appraisal</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div>
        <div class="font-serif text-xl mb-6">Contact</div>
        <div class="space-y-3 text-white/70">
          <div>Rizal Tuy</div>
          <div>Batangas, Philippines</div>
          <div>+63 945-406-0982</div>
          <div>auagjewerlyaccessories@gmail.com</div>
        </div>
      </div>
    </div>

    <div class="border-t border-white/10">
      <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-white/50">
          <p>© 2024 AUAG Jewelry. All rights reserved.</p>
          <div class="flex gap-6">
            <a href="#" class="hover:text-white transition-colors duration-200">Privacy Policy</a>
            <a href="#" class="hover:text-white transition-colors duration-200">Terms of Service</a>
            <a href="#" class="hover:text-white transition-colors duration-200">Cookie Policy</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- JavaScript -->
  <script>
    // Scroll to section function
    function scrollToSection(sectionId) {
      const element = document.querySelector(sectionId);
      if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
      }
    }

    // Carousel functionality
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

    // Newsletter success
    function showNewsletterSuccess() {
      alert('Thank you for subscribing to our newsletter!');
    }
  </script>
</body>
</html>
