<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Shop') }}
        </h2>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            [x-cloak] { display: none !important; }
            .line-clamp-1 { overflow: hidden; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; }
            .line-clamp-2 { overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
            .line-clamp-3 { overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; }

            /* Custom animations */
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .animate-fade-in {
                animation: fadeIn 0.3s ease-out;
            }

            /* Consistent image sizing */
            .product-image {
                width: 100%;
                height: 280px;
                object-fit: cover;
                object-position: center;
            }

            .modal-image {
                width: 100%;
                height: 320px;
                object-fit: contain;
                object-position: center;
                background: #f8fafc;
            }

            .dark .modal-image {
                background: #374151;
            }

            /* Smooth transitions */
            .smooth-transition {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            /* Gradient backgrounds */
            .gradient-bg {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }

            .gradient-text {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
        </style>
    </x-slot>

    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="fixed top-4 right-4 z-50 px-6 py-3 bg-green-500 text-white rounded-lg shadow-lg animate-fade-in">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="fixed top-4 right-4 z-50 px-6 py-3 bg-red-500 text-white rounded-lg shadow-lg animate-fade-in">
            {{ session('error') }}
        </div>
    @endif

    <div x-data="shop()" x-init="init()" class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Mobile Filter Button --}}
            <div class="lg:hidden flex items-center justify-between mb-8">
                <button @click="openFilters = !openFilters"
                        class="flex items-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 smooth-transition font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filters
                </button>

                {{-- Sort Dropdown Mobile --}}
                <div class="relative">
                    <select x-model="sort" @change="applyFilters()" class="block w-full pl-3 pr-10 py-3 text-base border border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl">
                        <option value="newest">Newest</option>
                        <option value="recommended">Recommended</option>
                        <option value="popular">Popular</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-8">
                {{-- Main Content - Products --}}
                <div class="flex-1">
                    {{-- Header with Search --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8">
                        <div class="flex-1 max-w-lg">
                            <form method="GET" action="{{ route('shop.index') }}" id="searchForm">
                                <div class="relative">
                                    <input type="text"
                                           name="search"
                                           value="{{ request('search') }}"
                                           x-model="searchQuery"
                                           placeholder="Search products..."
                                           class="w-full pl-12 pr-4 py-4 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <input type="hidden" name="sort" x-model="sort">
                                <input type="hidden" name="min_price" x-model="filters.min_price">
                                <input type="hidden" name="max_price" x-model="filters.max_price">
                                <template x-for="category in filters.categories" :key="category">
                                    <input type="hidden" name="category[]" :value="category">
                                </template>
                            </form>
                        </div>

                        {{-- Desktop Sort --}}
                        <div class="hidden lg:block">
                            <select x-model="sort" @change="applyFilters()" class="block w-full pl-4 pr-10 py-3 text-base border border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl shadow-sm">
                                <option value="newest">Newest</option>
                                <option value="recommended">Recommended</option>
                                <option value="popular">Popular</option>
                                <option value="price-low">Price: Low to High</option>
                                <option value="price-high">Price: High to Low</option>
                            </select>
                        </div>
                    </div>

                    {{-- Product Count & View Toggle --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                        <p class="text-gray-600 dark:text-gray-400 font-medium">
                            Showing <span class="text-gray-900 dark:text-white">{{ $products->count() }}</span> of <span class="text-gray-900 dark:text-white">{{ $products->total() }}</span> products
                            @if(request('search'))
                                <span class="text-sm">for "{{ request('search') }}"</span>
                            @endif
                        </p>
                        <div class="flex items-center gap-2 bg-white dark:bg-gray-800 p-1 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <button @click="gridView = 'grid'"
                                    :class="gridView === 'grid' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                                    class="p-2 rounded-lg smooth-transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                            </button>
                            <button @click="gridView = 'list'"
                                    :class="gridView === 'list' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                                    class="p-2 rounded-lg smooth-transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Products Grid --}}
                    @if($products->count() > 0)
                        <div :class="gridView === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6' : 'space-y-6'">
                            @foreach($products as $product)
                                <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-2xl smooth-transition overflow-hidden border border-gray-200 dark:border-gray-700"
                                     :class="gridView === 'list' ? 'flex flex-col md:flex-row' : ''">
                                    {{-- Image Container --}}
                                    <div class="relative overflow-hidden bg-gray-100 dark:bg-gray-700 cursor-pointer"
                                         :class="gridView === 'list' ? 'md:w-1/3' : ''"
                                         @click="openProductModal({{ $product }})">
                                        <img src="{{ $product->image_url ?: '/images/placeholder.jpg' }}"
                                             alt="{{ $product->name }}"
                                             class="product-image group-hover:scale-105 smooth-transition"
                                             :class="gridView === 'list' ? 'md:h-full' : ''">

                                        {{-- Category Badge --}}
                                        @if($product->category)
                                            <div class="absolute top-3 left-3">
                                                <span class="px-3 py-1 bg-black/80 backdrop-blur-sm text-white text-xs rounded-full font-medium">
                                                    {{ $product->category->name }}
                                                </span>
                                            </div>
                                        @endif

                                        {{-- Stock Status --}}
                                        <div class="absolute bottom-3 left-3">
                                            @if($product->quantity > 0)
                                                <span class="px-2 py-1 bg-green-500 text-white text-xs rounded-full font-medium">In Stock</span>
                                            @else
                                                <span class="px-2 py-1 bg-red-500 text-white text-xs rounded-full font-medium">Out of Stock</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Product Info --}}
                                    <div class="p-6 flex-1"
                                         :class="gridView === 'list' ? 'md:flex md:flex-col md:justify-between' : ''">
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white line-clamp-1 cursor-pointer text-lg mb-2"
                                                @click="openProductModal({{ $product }})">
                                                {{ $product->name }}
                                            </h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-4">{{ $product->size }}</p>
                                        </div>
                                        <div class="flex items-center justify-between mb-4">
                                            <span class="text-xl font-bold text-gray-900 dark:text-white">
                                                ₱{{ number_format($product->price, 2) }}
                                            </span>
                                            <span class="text-sm text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $product->style }}</span>
                                            <span class="text-sm text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $product->material }}</span>
                                        </div>

                                        {{-- Action Buttons --}}
                                        <div class="flex gap-3">
                                            <button @click="openProductModal({{ $product }})"
                                                    class="flex-1 px-4 py-3 bg-gray-900 text-white rounded-xl hover:bg-gray-800 smooth-transition text-center font-medium shadow-sm">
                                                View
                                            </button>

                                            <form action="{{ route('favorites.toggle', $product) }}" method="POST" @click.stop>
    @csrf
    <button type="submit"
        class="w-12 h-12 flex items-center justify-center rounded-xl shadow-sm smooth-transition"
        :class="{{ $product->is_favorite }} ? 'bg-red-500 text-white' : 'gradient-bg text-white'">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
    </button>
</form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- No Products Found --}}
                        <div class="col-span-full text-center py-16">
                            <div class="max-w-md mx-auto">
                                <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">No products found</h3>
                                <p class="text-gray-500 dark:text-gray-400 mb-6">
                                    @if(request('search'))
                                        Try adjusting your search to find what you're looking for.
                                    @else
                                        Try adjusting your filters to find what you're looking for.
                                    @endif
                                </p>
                                @if(request('search') || request('category') || request('min_price') || request('max_price'))
                                    <a href="{{ route('shop.index') }}" class="inline-flex items-center px-6 py-3 gradient-bg text-white rounded-xl hover:opacity-90 smooth-transition font-medium shadow-sm">
                                        Clear filters
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Pagination --}}
                    @if($products->hasPages())
                        <div class="mt-16 flex justify-center">
                            <div class="flex items-center gap-2">
                                {{ $products->links() }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Filters Sidebar --}}
                <div class="hidden lg:block w-80 flex-shrink-0">
                    {{-- Filters Card - Fixed Position --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-24">
                        {{-- Filters Header --}}
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h3>
                            <button @click="clearFilters()" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium smooth-transition">
                                Clear all
                            </button>
                        </div>

                        {{-- Filters Form --}}
                        <form method="GET" action="{{ route('shop.index') }}" id="filterForm">
                            <input type="hidden" name="sort" x-model="sort">
                            <input type="hidden" name="search" value="{{ request('search') }}">

                            {{-- Price Range --}}
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-4">Price Range</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                                        <span>Min: ₱0</span>
                                        <span>Max: ₱50,000</span>
                                    </div>
                                    <div class="flex gap-3">
                                        <input type="number"
                                               name="min_price"
                                               x-model="filters.min_price"
                                               value="{{ request('min_price') }}"
                                               placeholder="Min"
                                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <input type="number"
                                               name="max_price"
                                               x-model="filters.max_price"
                                               value="{{ request('max_price') }}"
                                               placeholder="Max"
                                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>

                            {{-- Categories --}}
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-4">Categories</h4>
                                <div class="space-y-3 max-h-60 overflow-y-auto">
                                    @php $selectedCats = (array) request('category', []); @endphp
                                    @forelse ($categories as $cat)
                                        <label class="flex items-center gap-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 px-3 rounded-lg smooth-transition">
                                            <input type="checkbox"
                                                   name="category[]"
                                                   value="{{ $cat->id }}"
                                                   x-model="filters.categories"
                                                   @checked(in_array($cat->id, $selectedCats))
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                            <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ $cat->name }}</span>
                                        </label>
                                    @empty
                                        <p class="text-sm text-gray-500">No categories found.</p>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Apply Filters Button --}}
                            <button type="submit"
                                    class="w-full gradient-bg text-white py-4 rounded-xl font-medium hover:opacity-90 smooth-transition shadow-sm">
                                Apply Filters
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile Filters Drawer --}}
        <div x-show="openFilters" class="lg:hidden" x-cloak>
            {{-- Backdrop --}}
            <div x-show="openFilters"
                 class="fixed inset-0 bg-black bg-opacity-50 z-40"
                 @click="openFilters = false">
            </div>

            {{-- Drawer --}}
            <div x-show="openFilters"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="transform translate-x-full"
                 x-transition:enter-end="transform translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="transform translate-x-0"
                 x-transition:leave-end="transform translate-x-full"
                 class="fixed top-0 right-0 h-full w-80 bg-white dark:bg-gray-800 shadow-xl z-50 overflow-y-auto">

                {{-- Drawer Header --}}
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h3>
                    <button @click="openFilters = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg smooth-transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Mobile Filters Content --}}
                <div class="p-6">
                    <form method="GET" action="{{ route('shop.index') }}" id="mobileFilterForm">
                        <input type="hidden" name="sort" x-model="sort">
                        <input type="hidden" name="search" value="{{ request('search') }}">

                        <div class="space-y-8">
                            {{-- Price Range --}}
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-4">Price Range</h4>
                                <div class="space-y-4">
                                    <div class="flex gap-3">
                                        <input type="number"
                                               name="min_price"
                                               x-model="filters.min_price"
                                               value="{{ request('min_price') }}"
                                               placeholder="Min"
                                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <input type="number"
                                               name="max_price"
                                               x-model="filters.max_price"
                                               value="{{ request('max_price') }}"
                                               placeholder="Max"
                                               class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>

                            {{-- Categories --}}
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-4">Categories</h4>
                                <div class="space-y-3 max-h-60 overflow-y-auto">
                                    @foreach ($categories as $cat)
                                        <label class="flex items-center gap-3 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 px-3 rounded-lg smooth-transition">
                                            <input type="checkbox"
                                                   name="category[]"
                                                   value="{{ $cat->id }}"
                                                   x-model="filters.categories"
                                                   @checked(in_array($cat->id, request('category', [])))
                                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                            <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ $cat->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Apply Filters Button --}}
                            <button type="submit"
                                    @click="openFilters = false"
                                    class="w-full gradient-bg text-white py-4 rounded-xl font-medium hover:opacity-90 smooth-transition shadow-sm">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Product Modal --}}
        <div x-show="productModalOpen" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div x-show="productModalOpen"
                     class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="productModalOpen = false">
                </div>

                {{-- Modal Panel --}}
                <div x-show="productModalOpen"
                     class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-2xl rounded-2xl max-h-[90vh] overflow-y-auto smooth-transition"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <div class="flex flex-col">
                        {{-- Product Image --}}
                        <div class="relative">
                            <img :src="selectedProduct.image_url || '/images/placeholder.jpg'"
                                 :alt="selectedProduct.name"
                                 class="modal-image">

                            {{-- Close Button --}}
                            <button @click="productModalOpen = false"
                                    class="absolute top-4 right-4 p-2 bg-black/20 backdrop-blur-sm text-white rounded-full hover:bg-black/30 smooth-transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                            {{-- Category Badge --}}
                            <div class="absolute top-4 left-4">
                                <span class="px-3 py-1 bg-black/80 backdrop-blur-sm text-white text-sm rounded-full font-medium"
                                      x-text="selectedProduct.category ? selectedProduct.category.name : 'Uncategorized'"></span>
                            </div>

                            {{-- Stock Status --}}
                            <div class="absolute bottom-4 left-4">
                                <span class="px-3 py-1 text-sm rounded-full font-medium"
                                      :class="selectedProduct.quantity > 0 ? 'bg-green-500 text-white' : 'bg-red-500 text-white'"
                                      x-text="selectedProduct.quantity > 0 ? 'In Stock' : 'Out of Stock'"></span>
                            </div>
                        </div>

                        {{-- Product Details --}}
                        <div class="p-6">
                            {{-- Product Info --}}
                            <div class="mb-6">
                                <div class="flex items-start justify-between mb-4">
                                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="selectedProduct.name"></h2>
                                    <span class="text-2xl font-bold text-gray-900 dark:text-white" x-text="'₱' + (selectedProduct.price ? selectedProduct.price.toLocaleString('en-PH', {minimumFractionDigits: 2}) : '0.00')"></span>
                                </div>

                                <p class="text-gray-600 dark:text-gray-400 mb-4 leading-relaxed" x-text="selectedProduct.description"></p>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-white mb-1">Style</h4>
                                        <p class="text-gray-600 dark:text-gray-400" x-text="selectedProduct.style || 'Not specified'"></p>
                                        <h4 class="font-medium text-gray-900 dark:text-white mb-1">Material</h4>
                                        <p class="text-gray-600 dark:text-gray-400" x-text="selectedProduct.material || 'Not specified'"></p>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-white mb-1">Available Quantity</h4>
                                        <p class="text-gray-600 dark:text-gray-400" x-text="selectedProduct.quantity || 0"></p>
                                        <h4 class="font-medium text-gray-900 dark:text-white mb-1">Size</h4>
                                        <p class="text-gray-600 dark:text-gray-400" x-text="selectedProduct.size || 'N/A"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex gap-3">
                                <form :action="'/favorites/' + selectedProduct.id + '/toggle'" method="POST" @click.stop>
    @csrf
    <button type="submit"
        class="w-full px-4 py-3 rounded-xl font-medium shadow-sm smooth-transition flex items-center justify-center gap-2"
            :class="{{ $product->is_favorite }} ? 'bg-red-500 text-white' : 'gradient-bg text-white'"
        @click="toggleFavorite(selectedProduct.id)">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
        <span x-text="selectedProduct.is_favorite ? 'Remove Favorite' : 'Add to Favorite'"></span>
    </button>
</form>


                                <button @click="productModalOpen = false"
                                        class="flex-1 px-4 py-3 bg-gray-800 text-white rounded-xl hover:bg-gray-700 smooth-transition font-medium shadow-sm">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    function shop() {
    return {
        productModalOpen: false,
        openFilters: false,
        searchQuery: '{{ request('search', '') }}',
        sort: '{{ request('sort', 'newest') }}',
        gridView: 'grid',
        favorites: [],
        selectedProduct: {},
        filters: {
            min_price: '{{ request('min_price', '') }}',
            max_price: '{{ request('max_price', '') }}',
            categories: @json(request('category', []))
        },

        init() {
                // Convert filter categories
                this.filters.categories = this.filters.categories.map(cat => cat.toString());

                // 1. Load localStorage favorites
                const saved = localStorage.getItem('favorites');
                this.favorites = saved ? JSON.parse(saved).map(id => Number(id)) : [];

                // 2. Load backend favorites from Blade
                const backendFavorites = @json($favoriteIds).map(id => Number(id));

                // 3. Merge backend favorites into local favorites
                backendFavorites.forEach(id => {
                    if (!this.favorites.includes(id)) {
                        this.favorites.push(id);
                    }
                });

                // 4. Save merged results
                localStorage.setItem('favorites', JSON.stringify(this.favorites));
            }
            ,

        loadFavorites() {
            const saved = localStorage.getItem('favorites');

            if (!saved) {
                this.favorites = [];
                return;
            }

            // 🔥 MOST IMPORTANT FIX: ALWAYS convert to numeric IDs
            this.favorites = JSON.parse(saved).map(id => Number(id));
        },

        saveFavorites() {
            // ensure only numbers stored
            localStorage.setItem('favorites', JSON.stringify(this.favorites));
        },

        toggleFavorite(productId) {
            productId = Number(productId); // 🔥 convert to number always

            const index = this.favorites.indexOf(productId);

            if (index > -1) {
                this.favorites.splice(index, 1);
            } else {
                this.favorites.push(productId);
            }

            this.saveFavorites();
        },

        openProductModal(product) {
            product.id = Number(product.id);
            this.selectedProduct = product;

            this.productModalOpen = true;

            axios.post(`/product/view/${product.id}`)
        },

        applyFilters() {
            document.getElementById('searchForm').submit();
        },

        clearFilters() {
            this.sort = 'newest';
            this.searchQuery = '';
            this.filters = {
                min_price: '',
                max_price: '',
                categories: []
            };
            window.location.href = '{{ route('shop.index') }}';
        }
    }
}
</script>
</x-app-layout>
