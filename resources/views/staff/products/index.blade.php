<x-staff-layout :navItems="$navItems ?? []">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 border-b border-gray-200 pb-6">
            <div>
                <h1 class="text-3xl font-serif font-bold text-gray-900 tracking-tight">
                    Product Inventory
                </h1>
                <p class="text-sm text-gray-500 mt-1">Manage your jewelry collection</p>
            </div>
        </div>

        {{-- FILTER FORM --}}
        <form method="GET" class="mb-10 bg-white p-6 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
            
            <div class="flex items-center gap-2 mb-6 border-b border-gray-100 pb-4">
                <div class="p-2 bg-yellow-50 rounded-lg text-yellow-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-800">Filter Collection</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">

                {{-- SEARCH --}}
                <div class="md:col-span-5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Search Name</label>
                    <div class="relative">
                        <input type="text" name="q" value="{{ $q ?? '' }}"
                            placeholder="Search by product name..."
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-yellow-500 focus:border-yellow-500 block p-3.5 shadow-sm transition-colors">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                </div>

                {{-- CATEGORY --}}
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Category</label>
                    <div class="relative">
                        <select name="category_id"
                                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-yellow-500 focus:border-yellow-500 block p-3.5 shadow-sm appearance-none">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ ($category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div class="md:col-span-4 flex gap-3">
                    <button class="flex-1 px-6 py-3.5 bg-gradient-to-r from-yellow-600 to-yellow-500 text-white rounded-xl hover:from-yellow-500 hover:to-yellow-400 shadow-md shadow-yellow-200 transition font-medium text-sm">
                        Apply Filters
                    </button>
                    
                    <a href="{{ route('staff.products.index') }}"
                       class="px-6 py-3.5 bg-white text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 hover:text-gray-800 transition font-medium text-sm">
                        Reset
                    </a>
                </div>

            </div>
        </form>

        {{-- PRODUCT LIST CARD --}}
        <div class="bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl border border-gray-100 overflow-hidden">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h2 class="text-gray-800 font-bold">Inventory List</h2>
                <span class="px-3 py-1 bg-gray-200 text-gray-600 rounded-full text-xs font-semibold">{{ $products->total() }} Items</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 font-bold text-left text-gray-500 uppercase tracking-wider text-xs w-20">Image</th>
                            <th class="px-6 py-4 font-bold text-left text-gray-500 uppercase tracking-wider text-xs">Product Details</th>
                            <th class="px-6 py-4 font-bold text-left text-gray-500 uppercase tracking-wider text-xs">Category</th>
                            <th class="px-6 py-4 font-bold text-left text-gray-500 uppercase tracking-wider text-xs">Attributes</th>
                            <th class="px-6 py-4 font-bold text-right text-gray-500 uppercase tracking-wider text-xs">Price</th>
                            <th class="px-6 py-4 font-bold text-right text-gray-500 uppercase tracking-wider text-xs">Stock</th>
                            <th class="px-6 py-4 font-bold text-center text-gray-500 uppercase tracking-wider text-xs">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($products as $product)
                            <tr class="hover:bg-yellow-50/30 transition duration-150 ease-in-out group">

                                {{-- IMAGE --}}
                                <td class="px-6 py-4 align-middle">
                                    <div class="h-14 w-14 rounded-xl overflow-hidden border border-gray-200 shadow-sm group-hover:border-yellow-300 transition">
                                        @if($product->pictureUrl)
                                            <img src="{{ asset('storage/'. $product->pictureUrl->url) }}"
                                                 class="h-full w-full object-cover">
                                        @else
                                            <div class="h-full w-full bg-gray-50 flex items-center justify-center text-[10px] text-gray-400 text-center leading-tight">
                                                No<br>Img
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- NAME --}}
                                <td class="px-6 py-4 align-middle">
                                    <div class="text-gray-900 font-bold text-base">{{ $product->name }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5 font-mono">ID: #{{ $product->id }}</div>
                                </td>

                                {{-- CATEGORY --}}
                                <td class="px-6 py-4 align-middle">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                        {{ $product->category->name ?? 'Uncategorized' }}
                                    </span>
                                </td>

                                {{-- ATTRIBUTES --}}
                                <td class="px-6 py-4 align-middle">
                                    <div class="text-gray-600 text-xs">
                                        <span class="text-gray-400 uppercase text-[10px]">Mat:</span> {{ ucfirst($product->material) ?? '-' }}
                                    </div>
                                    <div class="text-gray-600 text-xs mt-1">
                                        <span class="text-gray-400 uppercase text-[10px]">Style:</span> {{ ucfirst($product->style) ?? '-' }}
                                    </div>
                                </td>

                                {{-- PRICE --}}
                                <td class="px-6 py-4 align-middle text-right">
                                    <div class="font-serif text-yellow-700 font-bold text-lg">
                                        ₱{{ number_format($product->price, 2) }}
                                    </div>
                                </td>

                                {{-- STOCK --}}
                                <td class="px-6 py-4 align-middle text-right">
                                    @if($product->quantity < 5)
                                        <span class="text-red-500 font-bold bg-red-50 px-2 py-1 rounded">{{ $product->quantity }}</span>
                                    @elseif($product->quantity < 10)
                                        <span class="text-orange-500 font-bold bg-orange-50 px-2 py-1 rounded">{{ $product->quantity }}</span>
                                    @else
                                        <span class="text-gray-600 font-medium">{{ $product->quantity }}</span>
                                    @endif
                                </td>

                                {{-- STATUS --}}
                                <td class="px-6 py-4 align-middle text-center">
                                    @if ($product->status)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-100">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- EMPTY STATE --}}
            @if ($products->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 px-4 text-center bg-gray-50/50">
                    <div class="bg-white rounded-full p-4 mb-4 shadow-sm border border-gray-100">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No products found</h3>
                    <p class="text-gray-500 mt-1 max-w-sm text-sm">
                        We couldn't find any products matching your search filters. Try adjusting them.
                    </p>
                </div>
            @endif
        </div>

        {{-- PAGINATION --}}
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </div>

</x-staff-layout>