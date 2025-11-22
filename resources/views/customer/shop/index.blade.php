<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Shop') }}
        </h2>
          @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    .no-scrollbar::-webkit-scrollbar{display:none}
    .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
  </style>
        <style>[x-cloak]{display:none!important}</style>

    </x-slot>


    <div x-data="{ openFilters:false }" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <section aria-labelledby="filter-heading" class="grid items-center border-t border-b border-gray-200 pb-10">
            <h2 id="filter-heading" class="sr-only">Filters</h2>

            {{-- Top Row: left toggle + clear, right sort --}}
            <div class="relative col-start-1 row-start-1 py-4">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 text-sm sm:px-6 lg:px-8">
                <div class="flex items-center gap-6">
                    <button type="button"
                            @click="openFilters = !openFilters"
                            :aria-expanded="openFilters"
                            class="group flex items-center font-medium text-gray-700">
                    {{-- funnel icon --}}
                    <svg class="mr-2 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M2.63 1.6C5.03 1.21 7.49 1 10 1s4.97.21 7.37.6c.38.06.63.38.63.75v2.29c0 .6-.24 1.17-.66 1.59l-4.68 4.68c-.42.42-.66.99-.66 1.59v3.04c0 .68-.31 1.33-.84 1.76l-1.94 1.55A.75.75 0 018 18.25v-5.76c0-.6-.24-1.17-.66-1.59L2.66 6.22A2.25 2.25 0 012 4.63V2.34c0-.37.25-.69.63-.75Z"/>
                    </svg>
                    {{ $activeFilterCount ?? 0 }} Filters
                    </button>
                      <span class="h-5 w-px bg-bg-black"></span>

                    <a href="{{ route('shop.index') }}" class="text-gray-500">Clear all</a>
                </div>

                {{-- Right: Sort dropdown (keeps current filters) --}}
                <div class="relative">
                    <details class="group">
                    <summary class="list-none cursor-pointer select-none text-gray-700 hover:text-gray-900 flex items-center gap-1">
                        Sort
                        <svg class="h-5 w-5 text-gray-400 group-open:rotate-180 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"/>
                        </svg>
                    </summary>
                    <div class="absolute right-0 mt-2 w-40 rounded-md bg-white shadow-2xl ring-1 ring-black/5 py-1">
                        <a class="block px-4 py-2 text-sm {{ request('sort')==='popular'?'font-medium text-gray-900':'text-gray-600' }}"
                        href="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}">Popular</a>
                        <a class="block px-4 py-2 text-sm {{ request('sort')==='newest'?'font-medium text-gray-900':'text-gray-600' }}"
                        href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}">Newest</a>
                    </div>
                    </details>
                </div>
                </div>
            </div>

            {{-- Filters Panel --}}
            <form method="GET" x-data @change="$el.requestSubmit()">
                {{-- Keep current sort when changing filters --}}
                <input type="hidden" name="sort" value="{{ request('sort') }}">

                <div x-cloak x-show="openFilters" x-transition.opacity class="border-t border-gray-200 py-8">
                <div class="border-1 mx-auto grid max-w-7xl grid-cols-1 gap-8 px-4 text-sm sm:grid-cols-2 sm:px-6 lg:grid-cols-3 lg:px-8">

                    {{-- Price Order (High/Low) --}}
                    <fieldset class="mt-2">
                    <legend class="m-2 block font-medium">Price</legend>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3">
                        <input type="radio" name="price_order" value="desc"
                                @checked(request('price_order')==='desc') class="h-4 w-4">
                        <span class="text-gray-700">High to Low</span>
                        </label>
                        <label class="flex items-center gap-3">
                        <input type="radio" name="price_order" value="asc"
                                @checked(request('price_order')==='asc') class="h-4 w-4">
                        <span class="text-gray-700">Low to High</span>
                        </label>
                        <label class="flex items-center gap-3">
                        <input type="radio" name="price_order" value=""
                                @checked(!request()->filled('price_order')) class="h-4 w-4"
                                onclick="this.form.price_order.value=''; this.form.requestSubmit();">
                        <span class="text-gray-500">No price ordering</span>
                        </label>
                    </div>
                    </fieldset>

                    {{-- Categories (from DB) --}}
                        <fieldset class="sm:col-span-2 lg:col-span-2">
                        <legend class="mb-3 block font-medium">Category</legend>

                        @php $selectedCats = (array) request('category'); @endphp

                        <div class="grid grid-cols-2 gap-y-3 gap-x-6">
                            @forelse ($categories as $cat)
                            <label class="flex items-center gap-3">
                                <input
                                type="checkbox"
                                name="category[]"
                                value="{{ $cat->id }}"            {{-- use ID --}}
                                @checked(in_array($cat->id, $selectedCats))
                                class="h-4 w-4"
                                >
                                <span class="text-gray-700">{{ $cat->name }}</span>
                            </label>
                            @empty
                            <p class="col-span-2 text-gray-500">No categories found.</p>
                            @endforelse
                        </div>
                        </fieldset>

                </div>
                </div>
            </form>
            </section>

        

            <div class="bg-transparent mt-5">
            <div class="mx-auto max-w-2xl px-0 sm:px-0 lg:max-w-7xl lg:px-0">
                <div class="md:flex md:items-center md:justify-between">
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Trending products</h2>

                <a href="{{ route('shop.index') }}" class="hidden text-sm font-medium text-indigo-600 hover:text-indigo-500 md:block">
                    Shop the collection
                    <span aria-hidden="true"> &rarr;</span>
                </a>
                </div>

                {{-- PRODUCT CARDS --}}
                <div class="mt-3 grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-x-12 gap-y-16">
                @forelse ($products as $product)
                    @php
                    $rawImg = data_get($product, 'primaryPicture.url') ?? data_get($product, 'pictures.0.url');
                    $img = \Illuminate\Support\Str::startsWith($rawImg, ['http://','https://'])
                        ? ($rawImg ?? 'https://images.unsplash.com/photo-1603575449141-2a7c43e6c0aa?q=80&w=1400&auto=format&fit=crop')
                        : ($rawImg ? asset('storage/'.$rawImg) : 'https://images.unsplash.com/photo-1603575449141-2a7c43e6c0aa?q=80&w=1400&auto=format&fit=crop');

                    $sub = $product->variant
                        ?? optional($product->category)->name
                        ?? \Illuminate\Support\Str::limit(strip_tags($product->description), 32);
                    @endphp

                    <div>
                    {{-- Product image + overlay price --}}
                    <div class="relative p-2">
                        <div class="relative h-72 w-full overflow-hidden rounded-lg">
                        <img src="{{ $img }}" alt="{{ $product->name }}" class="size-full object-cover" />
                        </div>

                        <div class="relative mt-4">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                            <a href="#">
                            <span class="absolute inset-0"></span>
                            {{ $product->name }}
                            </a>
                        </h3>
                        @if($sub)
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $sub }} - {{ $product->description}}</p>
                        @endif
                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white"> ₱{{ number_format((float)($product->price ?? 0), 2) }} </p>
                        </div>


                    </div>

                    </div>
                @empty
                    <div class="col-span-full text-center text-gray-600 dark:text-gray-300">
                    No products found.
                    </div>
                @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-10">
                {{ $products->links() }}
                </div>
            </div>
            </div>

</x-app-layout>
