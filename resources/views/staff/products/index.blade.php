<x-staff-layout :navItems="$navItems ?? []">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 border-b pb-4">
            <h1 class="text-3xl font-extrabold text-gray-800 mb-4 sm:mb-0">Product Inventory 📦</h1>
        </div>

        <form method="GET"
            class="mb-8 bg-white p-6 rounded-2xl shadow-lg border border-gray-100">

            <h2 class="text-xl font-semibold text-gray-700 mb-4">Filter Products</h2>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">

                {{-- SEARCH --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Name</label>
                    <input type="text" name="q" value="{{ $q ?? '' }}"
                        placeholder="Search product by name..."
                        class="border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-xl p-3 w-full">
                </div>

                {{-- CATEGORY --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id"
                            class="border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 rounded-xl p-3 w-full">
                        <option value="">All Categories</option>

                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ ($category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- APPLY --}}
                <div>
                    <button class="w-full px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                        Apply
                    </button>
                </div>

                {{-- CLEAR BUTTON --}}
                <div>
                    <a href="{{ route('staff.products.index') }}"
                    class="w-full inline-flex justify-center px-6 py-3 bg-gray-200 text-gray-700 
                            rounded-xl hover:bg-gray-300 transition">
                        Clear
                    </a>
                </div>

            </div>
        </form>


        {{-- PRODUCT LIST --}}
        <div class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">

            <h2 class="text-xl font-semibold text-gray-700 mb-4">
                Product List ({{ $products->total() }})
            </h2>

            <div class="overflow-x-auto">

                <table class="min-w-full text-sm divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 font-bold text-left text-gray-600 uppercase tracking-wider w-16">Image</th>
                            <th class="px-4 py-3 font-bold text-left text-gray-600 uppercase tracking-wider">Product Name</th>
                            <th class="px-4 py-3 font-bold text-left text-gray-600 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 font-bold text-left text-gray-600 uppercase tracking-wider">Material</th>
                            <th class="px-4 py-3 font-bold text-left text-gray-600 uppercase tracking-wider">Style</th>
                            <th class="px-4 py-3 font-bold text-right text-gray-600 uppercase tracking-wider">Price</th>
                            <th class="px-4 py-3 font-bold text-right text-gray-600 uppercase tracking-wider">Stock</th>
                            <th class="px-4 py-3 font-bold text-left text-gray-600 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @foreach ($products as $product)
                            <tr class="hover:bg-indigo-50/20 transition">

                                {{-- IMAGE --}}
                                <td class="px-4 py-3">
                                    @if($product->pictureUrl)
                                        <img src="/{{ $product->pictureUrl->url }}"
                                             class="h-12 w-12 object-cover rounded-lg border shadow-sm">
                                    @else
                                        <div class="h-12 w-12 bg-gray-200 rounded-lg flex items-center justify-center text-xs text-gray-500">
                                            No Image
                                        </div>
                                    @endif
                                </td>

                                {{-- NAME --}}
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $product->name }}
                                </td>

                                {{-- CATEGORY --}}
                                <td class="px-4 py-3">
                                    <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-700">
                                        {{ $product->category->name ?? 'N/A' }}
                                    </span>
                                </td>

                                {{-- MATERIAL --}}
                                <td class="px-4 py-3 text-gray-700">
                                    {{ ucfirst($product->material) ?? '-' }}
                                </td>

                                {{-- STYLE --}}
                                <td class="px-4 py-3 text-gray-700">
                                    {{ ucfirst($product->style) ?? '-' }}
                                </td>

                                {{-- PRICE --}}
                                <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                    ₱{{ number_format($product->price, 2) }}
                                </td>

                                {{-- STOCK --}}
                                <td class="px-4 py-3 text-right">
                                    <span class="{{ $product->quantity < 10 ? 'text-red-600 font-bold' : 'text-gray-700' }}">
                                        {{ $product->quantity }}
                                    </span>
                                </td>

                                {{-- STATUS --}}
                                <td class="px-4 py-3">
                                    @if ($product->status)
                                        <span class="inline-flex items-center px-3 py-1 text-xs rounded-full bg-green-50 text-green-700">
                                            <span class="h-2 w-2 bg-green-500 rounded-full mr-1"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 text-xs rounded-full bg-red-50 text-red-700">
                                            <span class="h-2 w-2 bg-red-500 rounded-full mr-1"></span>
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
                <div class="text-center py-10 text-gray-500">
                    <h3 class="text-sm font-medium text-gray-900">No products found</h3>
                    <p class="text-sm text-gray-500">Try adjusting your filters or add a new product.</p>

                    <a href="{{ route('staff.products.create') }}"
                       class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        + Add Product
                    </a>
                </div>
            @endif
        </div>

        {{-- PAGINATION --}}
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </div>

</x-staff-layout>
