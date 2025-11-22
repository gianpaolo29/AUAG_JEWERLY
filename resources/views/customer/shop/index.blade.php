<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Shop') }}
        </h2>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        </style>
        <style>[x-cloak] { display: none !important; }</style>
    </x-slot>

    <div x-data="shop()" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Mobile Filter Button --}}
            <div class="lg:hidden flex items-center justify-between mb-6">
                <button @click="openFilters = !openFilters"
                        class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filters
                </button>
                
                {{-- Sort Dropdown Mobile --}}
                <div class="relative">
                    <select x-model="sort" @change="applyFilters()" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg">
                        <option value="popular">Popular</option>
                        <option value="newest">Newest</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-8">
                {{-- Main Content - Products --}}
                <div class="flex-1">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Shop Collection</h1>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Discover our exclusive jewelry collection
                            </p>
                        </div>
                        
                        {{-- Desktop Sort --}}
                        <div class="hidden lg:block">
                            <select x-model="sort" @change="applyFilters()" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg">
                                <option value="popular">Popular</option>
                                <option value="newest">Newest</option>
                                <option value="price-low">Price: Low to High</option>
                                <option value="price-high">Price: High to Low</option>
                            </select>
                        </div>
                    </div>

                    {{-- Product Count & View Toggle --}}
                    <div class="flex items-center justify-between mb-6">
                        <p class="text-gray-600 dark:text-gray-400">
                            Showing {{ $products->count() }} products
                        </p>
                        <div class="flex items-center gap-2">
                            <button class="p-2 rounded-lg border border-gray-300 dark:border-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                            </button>
                            <button class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Products Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @forelse ($products as $product)
                            <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer"
                                 @click="openProductModal({{ json_encode($product) }})">
                                {{-- Image Container --}}
                                <div class="relative overflow-hidden bg-gray-100 dark:bg-gray-700">
                                    <img src="{{ $product->image_url }}" 
                                         alt="{{ $product->name }}"
                                         class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-500">
                                    
                                    {{-- Favorite Button --}}
                                    <button @click.stop="toggleFavorite({{ $product->id }})"
                                            class="absolute top-3 right-3 p-2 bg-white/90 backdrop-blur-sm rounded-full shadow-lg hover:bg-white transition-colors z-10">
                                        <svg x-show="!favorites.includes({{ $product->id }})" 
                                             class="w-4 h-4 text-gray-600" 
                                             fill="none" 
                                             stroke="currentColor" 
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                        <svg x-show="favorites.includes({{ $product->id }})" 
                                             class="w-4 h-4 text-red-500" 
                                             fill="currentColor" 
                                             viewBox="0 0 24 24">
                                            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </button>
                                    
                                    {{-- Category Badge --}}
                                    @if($product->category)
                                        <div class="absolute top-3 left-3">
                                            <span class="px-3 py-1 bg-black/70 backdrop-blur-sm text-white text-xs rounded-full">
                                                {{ $product->category->name }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Product Info --}}
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 dark:text-white line-clamp-1">
                                        {{ $product->name }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                        {{ $product->description }}
                                    </p>
                                    <div class="mt-3">
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                                            ₱{{ number_format((float)($product->price ?? 0), 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <div class="max-w-md mx-auto">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No products found</h3>
                                    <p class="mt-2 text-gray-500 dark:text-gray-400">Try adjusting your filters to find what you're looking for.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    @if($products->hasPages())
                        <div class="mt-12 flex justify-center">
                            <div class="flex items-center gap-2">
                                {{ $products->links() }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Filters Sidebar --}}
                <div class="hidden lg:block w-80 flex-shrink-0">
                    <div class="sticky top-24 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        {{-- Filters Header --}}
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h3>
                            <button @click="clearFilters()" class="text-sm text-indigo-600 hover:text-indigo-500">
                                Clear all
                            </button>
                        </div>

                        {{-- Filters Form --}}
                        <form method="GET" class="space-y-6" id="filterForm">
                            <input type="hidden" name="sort" x-model="sort">

                            {{-- Price Range --}}
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-3">Price Range</h4>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Min: ₱0</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Max: ₱50,000</span>
                                    </div>
                                    <div class="flex gap-3">
                                        <input type="number" 
                                               name="min_price" 
                                               x-model="filters.min_price"
                                               placeholder="Min" 
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                                        <input type="number" 
                                               name="max_price" 
                                               x-model="filters.max_price"
                                               placeholder="Max" 
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                                    </div>
                                </div>
                            </div>

                            {{-- Categories --}}
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-3">Categories</h4>
                                <div class="space-y-2 max-h-60 overflow-y-auto no-scrollbar">
                                    @php $selectedCats = (array) request('category'); @endphp
                                    @forelse ($categories as $cat)
                                        <label class="flex items-center gap-3 py-1">
                                            <input type="checkbox" 
                                                   name="category[]" 
                                                   value="{{ $cat->id }}"
                                                   x-model="filters.categories"
                                                   @checked(in_array($cat->id, $selectedCats))
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $cat->name }}</span>
                                        </label>
                                    @empty
                                        <p class="text-sm text-gray-500">No categories found.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Apply Filters Button --}}
                            <button type="button" 
                                    @click="applyFilters()"
                                    class="w-full bg-black text-white py-3 rounded-lg font-medium hover:bg-gray-800 transition-colors">
                                Apply Filters
                            </button>
                        </form>
                    </div>

                    {{-- Recommended Section --}}
                    <div class="sticky top-[calc(100vh-200px)] mt-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Based on Your Favorites</h3>
                        <div class="space-y-3">
                            <template x-for="rec in recommendations" :key="rec.id">
                                <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                                     @click="openProductModal(rec)">
                                    <img :src="rec.image_url" :alt="rec.name" class="w-12 h-12 rounded-lg object-cover">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="rec.name"></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="'₱' + rec.price.toLocaleString()"></p>
                                    </div>
                                </div>
                            </template>
                            <div x-show="recommendations.length === 0" class="text-center py-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Favorite some items to get recommendations</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Product Modal --}}
        <div x-show="modalOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                     @click="modalOpen = false"></div>

                {{-- Modal Panel --}}
                <div class="relative inline-block w-full max-w-4xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-2xl rounded-2xl">
                    <div class="flex flex-col lg:flex-row">
                        {{-- Product Image --}}
                        <div class="lg:w-1/2">
                            <img :src="currentProduct.image_url" 
                                 :alt="currentProduct.name"
                                 class="w-full h-96 lg:h-full object-cover">
                        </div>

                        {{-- Product Details --}}
                        <div class="lg:w-1/2 p-6 lg:p-8">
                            {{-- Header --}}
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="currentProduct.name"></h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-text="currentProduct.category?.name || 'Jewelry'"></p>
                                </div>
                                <button @click="modalOpen = false" 
                                        class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Price --}}
                            <div class="mb-6">
                                <span class="text-3xl font-bold text-gray-900 dark:text-white" 
                                      x-text="'₱' + (currentProduct.price ? currentProduct.price.toLocaleString('en-PH', {minimumFractionDigits: 2}) : '0.00')"></span>
                            </div>

                            {{-- Description --}}
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Description</h3>
                                <p class="text-gray-600 dark:text-gray-300 leading-relaxed" x-text="currentProduct.description"></p>
                            </div>

                            {{-- Details --}}
                            <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">Material:</span>
                                    <span class="text-gray-600 dark:text-gray-300 ml-2" x-text="currentProduct.material || 'Gold'"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">Stone:</span>
                                    <span class="text-gray-600 dark:text-gray-300 ml-2" x-text="currentProduct.stone || 'Diamond'"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">Size:</span>
                                    <span class="text-gray-600 dark:text-gray-300 ml-2" x-text="currentProduct.size || 'Standard'"></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">In Stock:</span>
                                    <span class="text-green-600 ml-2">Available</span>
                                </div>
                            </div>

                            {{-- Favorite Button Only --}}
                            <div class="flex items-center">
                                <button @click="toggleFavorite(currentProduct.id)"
                                        class="flex items-center gap-2 px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <svg x-show="!favorites.includes(currentProduct.id)" 
                                         class="w-5 h-5 text-gray-600" 
                                         fill="none" 
                                         stroke="currentColor" 
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    <svg x-show="favorites.includes(currentProduct.id)" 
                                         class="w-5 h-5 text-red-500" 
                                         fill="currentColor" 
                                         viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                    <span x-text="favorites.includes(currentProduct.id) ? 'Remove Favorite' : 'Add to Favorites'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile Filters Drawer --}}
        <div x-show="openFilters" class="lg:hidden">
            {{-- Backdrop --}}
            <div x-show="openFilters" 
                 class="fixed inset-0 bg-black bg-opacity-50 z-40"
                 @click="openFilters = false">
            </div>

            {{-- Drawer --}}
            <div x-show="openFilters"
                 class="fixed top-0 right-0 h-full w-80 bg-white dark:bg-gray-800 shadow-xl z-50 overflow-y-auto">
                
                {{-- Drawer Header --}}
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h3>
                    <button @click="openFilters = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Mobile Filters Content --}}
                <div class="p-4">
                    {{-- Include the same filters form as desktop --}}
                    <div class="space-y-6">
                        {{-- Price Range --}}
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-3">Price Range</h4>
                            <div class="space-y-3">
                                <div class="flex gap-3">
                                    <input type="number" 
                                           x-model="filters.min_price"
                                           placeholder="Min" 
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                                    <input type="number" 
                                           x-model="filters.max_price"
                                           placeholder="Max" 
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                                </div>
                            </div>
                        </div>

                        {{-- Categories --}}
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-3">Categories</h4>
                            <div class="space-y-2 max-h-60 overflow-y-auto no-scrollbar">
                                @foreach ($categories as $cat)
                                    <label class="flex items-center gap-3 py-1">
                                        <input type="checkbox" 
                                               value="{{ $cat->id }}"
                                               x-model="filters.categories"
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $cat->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Apply Filters Button --}}
                        <button type="button" 
                                @click="applyFilters(); openFilters = false;"
                                class="w-full bg-black text-white py-3 rounded-lg font-medium hover:bg-gray-800 transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function shop() {
            return {
                modalOpen: false,
                openFilters: false,
                currentProduct: {},
                favorites: JSON.parse(localStorage.getItem('favorites')) || [],
                recommendations: [],
                sort: '{{ request('sort', 'popular') }}',
                filters: {
                    min_price: '{{ request('min_price', '') }}',
                    max_price: '{{ request('max_price', '') }}',
                    categories: JSON.parse('{{ json_encode(request('category', [])) }}'.replace(/&quot;/g, '"'))
                },

                init() {
                    this.updateRecommendations();
                },

                openProductModal(product) {
                    this.currentProduct = product;
                    this.modalOpen = true;
                },

                toggleFavorite(productId) {
                    if (this.favorites.includes(productId)) {
                        this.favorites = this.favorites.filter(id => id !== productId);
                    } else {
                        this.favorites.push(productId);
                    }
                    localStorage.setItem('favorites', JSON.stringify(this.favorites));
                    this.updateRecommendations();
                },

                updateRecommendations() {
                    // Simulate recommendation algorithm based on favorites
                    // In a real app, this would be an API call
                    this.recommendations = [];
                    
                    if (this.favorites.length > 0) {
                        // Simple simulation - show first 3 products that match favorite categories
                        const favoriteProducts = @json($products->take(3));
                        this.recommendations = favoriteProducts.map(product => ({
                            ...product,
                            image_url: product.image_url,
                            price: parseFloat(product.price)
                        }));
                    }
                },

                applyFilters() {
                    const params = new URLSearchParams();
                    
                    if (this.sort) params.append('sort', this.sort);
                    if (this.filters.min_price) params.append('min_price', this.filters.min_price);
                    if (this.filters.max_price) params.append('max_price', this.filters.max_price);
                    this.filters.categories.forEach(cat => params.append('category[]', cat));
                    
                    window.location.href = '{{ route('shop.index') }}?' + params.toString();
                },

                clearFilters() {
                    this.sort = 'popular';
                    this.filters = {
                        min_price: '',
                        max_price: '',
                        categories: []
                    };
                    this.applyFilters();
                }
            }
        }
    </script>
</x-app-layout>