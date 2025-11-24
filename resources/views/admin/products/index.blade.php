<x-admin-layout>
    <div class="flex flex-col gap-6">

    {{-- 1. Header and Primary Action --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Products Management</h1>

        <a href="{{ route('admin.products.create') }}"
            class="inline-flex items-center gap-2 bg-orange-500 text-white font-semibold px-4 py-2.5 rounded-lg shadow-md hover:bg-orange-600 transition-all duration-150">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
            Add New Product
        </a>
    </div>

    {{-- 2. Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Total Products --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="h-2 bg-blue-500"></div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <p class="text-base font-semibold text-gray-600">Total Products</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($stats['total_products']) }}</p>
                </div>
                <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l10 5.5v11L12 22 2 17.5v-11L12 2z"/></svg>
                </div>
            </div>
        </div>

        {{-- Active Products --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="h-2 bg-emerald-500"></div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <p class="text-base font-semibold text-gray-600">Active Products</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($stats['active_products']) }}</p>
                </div>
                <div class="p-3 rounded-full bg-emerald-50 text-emerald-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 6L9 17l-5-5"/></svg>
                </div>
            </div>
        </div>

        {{-- Low Stock Alerts --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="h-2 bg-amber-500"></div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <p class="text-base font-semibold text-gray-600">
                        Low Stock Alerts <span class="text-sm text-gray-400 font-normal">(&lt; {{ $lowStock }})</span>
                    </p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($stats['low_stock']) }}</p>
                </div>
                <div class="p-3 rounded-full bg-amber-50 text-amber-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10.29 3.86L1.82 18h20.36L13.71 3.86zM12 9v4M12 17h.01"/></svg>
                </div>
            </div>
        </div>

        {{-- Categories --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="h-2 bg-purple-500"></div>
            <div class="p-4 flex items-center justify-between">
                <div>
                    <p class="text-base font-semibold text-gray-600">Categories</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ number_format($stats['categories']) }}</p>
                </div>
                <div class="p-3 rounded-full bg-purple-50 text-purple-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7v10h18V7M7 7v10M17 7v10"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Filters --}}
    <div class="bg-white p-5 rounded-xl shadow-lg border border-gray-100 flex flex-col gap-4">
        <form method="GET" class="flex flex-col gap-4" x-data x-on:change="this.$el.submit()">
            {{-- Search --}}
            <div class="relative w-full">
                <svg class="h-4 w-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 19l-6-6M5 11a6 6 0 1 1 12 0 6 6 0 0 1-12 0z"/></svg>
                <input type="search" name="q" value="{{ $q }}"
                    placeholder="Search products..."
                    class="w-full pl-9 pr-4 text-sm rounded-lg border-gray-300 focus:ring-violet-500 focus:border-violet-500 shadow-sm" />
            </div>

            {{-- Row 1: Category & Status --}}
            <div class="flex flex-wrap items-center gap-3">
                <select name="category_id" class="text-sm rounded-lg border-gray-300 focus:ring-violet-500 focus:border-violet-500 shadow-sm flex-grow min-w-[140px]">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected((int)$category_id === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>

                <select name="status" class="text-sm rounded-lg border-gray-300 focus:ring-violet-500 focus:border-violet-500 shadow-sm flex-grow min-w-[120px]">
                    <option value="">All Status</option>
                    <option value="active" @selected($status==='active')>Active</option>
                    <option value="inactive" @selected($status==='inactive')>Inactive</option>
                </select>
            </div>

            {{-- Row 2: Stock & Clear --}}
            <div class="flex flex-wrap items-center gap-3">
                <select name="stock_status" class="text-sm rounded-lg border-gray-300 focus:ring-violet-500 focus:border-violet-500 shadow-sm flex-grow min-w-[120px]">
                    <option value="">All Stock</option>
                    <option value="normal" @selected($stock_status==='normal')>Normal Stock</option>
                    <option value="low" @selected($stock_status==='low')>Low Stock</option>
                    <option value="out" @selected($stock_status==='out')>Out of Stock</option>
                </select>

                {{-- Hidden fields for sorting --}}
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="dir" value="{{ $dir }}">

                {{-- Clear Filters --}}
                <a href="{{ route('admin.products.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 font-medium whitespace-nowrap p-2 transition flex-shrink-0">
                    <span class="inline-flex items-center gap-1">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 4H8l-7 16h18a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zM12 9v6M9 12h6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Clear Filters
                    </span>
                </a>
            </div>
        </form>
    </div>

    {{-- 4. Product Table --}}
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-700 uppercase tracking-wider text-xs">
                @php
                $arrow = fn($k) => $sort === $k ? ($dir==='asc'?' ↑':' ↓') : '';
                $link  = fn($k) => request()->fullUrlWithQuery([
                    'sort'=>$k,
                    'dir'=> ($sort===$k && $dir==='asc') ? 'desc':'asc'
                ]);
                @endphp
                <tr>
                    <th class="px-5 py-3 text-left w-10">
                        <input type="checkbox" class="rounded text-violet-600 border-gray-300 focus:ring-violet-500">
                    </th>
                    <th class="px-5 py-3 text-left w-10">Image</th>
                    <th class="px-5 py-3 text-left whitespace-nowrap">
                        <a href="{{ $link('name') }}" class="hover:text-violet-600 transition">Product Name{!! $arrow('name') !!}</a>
                    </th>
                    <th class="px-5 py-3 text-left whitespace-nowrap">Category</th>
                    <th class="px-5 py-3 text-left whitespace-nowrap">Material</th>
                    <th class="px-5 py-3 text-left whitespace-nowrap">Style</th>
                    <th class="px-5 py-3 text-left whitespace-nowrap">
                        <a href="{{ $link('price') }}" class="hover:text-violet-600 transition">Price{!! $arrow('price') !!}</a>
                    </th>
                    <th class="px-5 py-3 text-left whitespace-nowrap">
                        <a href="{{ $link('quantity') }}" class="hover:text-violet-600 transition">Stock{!! $arrow('quantity') !!}</a>
                    </th>
                    <th class="px-5 py-3 text-left whitespace-nowrap">
                        Status{!! $arrow('status') !!}
                    </th>
                    <th class="px-5 py-3 text-center whitespace-nowrap w-[150px]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $p)
                <tr class="text-gray-700 transition duration-100 hover:bg-violet-50/50">
                    <td class="px-5 py-3 w-10">
                        <input type="checkbox" class="rounded text-violet-600 border-gray-300 focus:ring-violet-500">
                    </td>
                    <td class="px-5 py-3 w-10">
                        <div class="h-10 w-10 rounded-lg bg-gray-100 overflow-hidden grid place-content-center border border-gray-200">
                            @if ($p->pictureUrl)
                                <img
                                    src="/{{ $p->pictureUrl->url }}"                                    class="h-10 w-10 object-cover"
                                    alt="{{ $p->name }}"
                                >
                            @else
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path d="M4 5h16v14H4zM4 15l4-4 4 4 4-3 4 3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-3 font-medium text-gray-900 whitespace-nowrap">
                        {{ $p->name }}
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-300">
                            {{ $p->category?->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <span class="text-sm text-gray-700">
                            {{ $p->material ? ucfirst($p->material) : '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <span class="text-sm text-gray-700">
                            {{ $p->style ? ucfirst($p->style) : '—' }}
                        </span>
                    </td>
                    {{-- Price --}}
                    <td class="px-5 py-3 font-extrabold whitespace-nowrap text-gray-800">
                        ₱{{ number_format($p->price,2) }}
                    </td>
                    {{-- Stock --}}
                    <td class="px-5 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-semibold whitespace-nowrap
                            @if($p->quantity <= 0)
                                bg-rose-100 text-rose-800
                            @elseif($p->quantity < $lowStock)
                                bg-amber-100 text-amber-800
                            @else
                                bg-emerald-100 text-emerald-800
                            @endif">
                            {{ $p->quantity <= 0 ? 'Out of Stock' : $p->quantity }}
                        </span>
                    </td>
                    {{-- Status --}}
                    <td class="px-5 py-3 whitespace-nowrap">
                        <form method="POST" action="{{ route('admin.products.toggle',$p) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-semibold whitespace-nowrap transition
                                {{ $p->status
                                    ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                {{ $p->status ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    {{-- Actions --}}
                    <td class="px-5 py-3 text-center whitespace-nowrap">
                        <div class="inline-flex items-center justify-center gap-1">
                            <a href="{{ route('admin.products.edit',$p) }}"
                               class="text-violet-600 hover:text-violet-700 p-2 rounded-lg hover:bg-violet-100 transition duration-150">
                               <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.products.destroy',$p) }}"
                                  onsubmit="return confirm('Are you sure you want to delete {{ $p->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-rose-600 hover:text-rose-700 p-2 rounded-lg hover:bg-rose-100 transition duration-150">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-5 py-10 text-center text-gray-500 text-base italic bg-gray-50">
                        <svg class="h-6 w-6 inline-block mb-2 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18h20.36L13.71 3.86zM12 9v4M12 17h.01"/></svg>
                        <p>No products match your current filters. Try broadening your search or adding a new product!</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="px-5 py-4 border-t border-gray-200 flex justify-between items-center bg-white">
            <p class="text-sm text-gray-600 hidden sm:block">
                Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results.
            </p>
            {{ $products->links() }}
        </div>
    </div>
    </div>
</x-admin-layout>
