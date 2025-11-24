<x-staff-layout title="New Transaction">

<div class="max-w-6xl mx-auto py-8"
     x-data="{
        search: '',
        products: @js($products),
        items: [],

        filtered() {
            if (!this.search) return this.products;
            const s = this.search.toLowerCase();
            return this.products.filter(p =>
                (p.name || '').toLowerCase().includes(s)
            );
        },

        add(p) {
            const max = Number(p.stock || 0);
            if (max <= 0) return;

            let found = this.items.find(i => i.product_id === p.id);

            if (found) {
                if (found.quantity < max) found.quantity++;
                return;
            }

            this.items.push({
                product_id: p.id,
                name: p.name,
                price: Number(p.price),
                quantity: 1,
                stock: max,
                image: p.image_url,
            });
        },

        remove(i) {
            this.items.splice(i, 1);
        },

        subtotal() {
            return this.items.reduce((t, i) => t + (i.price * i.quantity), 0);
        },

        money(v) {
            return '₱' + Number(v).toLocaleString('en-PH', {
                minimumFractionDigits: 2
            });
        }
     }">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Record New Sale</h1>
        <a href="{{ route('staff.transactions.index') }}"
           class="px-4 py-2 rounded bg-gray-100 text-gray-800 hover:bg-gray-200">
            Back
        </a>
    </div>

    {{-- FORM --}}
    <form action="{{ route('staff.transactions.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- LEFT: PRODUCT SELECTOR --}}
            <div class="bg-white rounded-xl shadow p-6">

                <h2 class="text-lg font-semibold mb-4">Select Product</h2>

                <input type="text"
                       x-model="search"
                       placeholder="Search product..."
                       class="w-full px-4 py-2 border rounded-lg mb-4 focus:ring-indigo-500 focus:border-indigo-500">

                <div class="max-h-[470px] overflow-y-auto space-y-3 pr-2">

                    <template x-for="p in filtered()" :key="p.id">
                        <button
                            type="button"
                            @click="add(p)"
                            class="w-full flex items-center justify-between border rounded-lg p-3 hover:bg-gray-50">

                            <div class="flex items-center gap-3">
                                <img :src="p.image_url || '{{ asset('images/placeholder-product.png') }}'"
                                     class="w-12 h-12 rounded object-cover border">
                                <div>
                                    <p class="font-medium" x-text="p.name"></p>
                                    <p class="text-xs text-gray-500" x-text="money(p.price)"></p>
                                    <p class="text-[11px] text-gray-400">Stock: <span x-text="p.stock"></span></p>
                                </div>
                            </div>

                            <span class="px-3 py-1.5 bg-indigo-600 text-white text-xs rounded-lg">
                                Add
                            </span>
                        </button>
                    </template>

                    <template x-if="filtered().length === 0">
                        <p class="text-gray-500 text-sm">No products found.</p>
                    </template>

                </div>

            </div>

            {{-- RIGHT: CART + SUMMARY --}}
            <div class="bg-white rounded-xl shadow p-6">

                <h2 class="text-lg font-semibold mb-4">Cart</h2>

                <template x-if="items.length === 0">
                    <p class="text-gray-500 text-sm">No items added yet.</p>
                </template>

                <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2">

                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-4 border rounded-xl flex justify-between items-center bg-gray-50">

                            <div class="flex items-center gap-3">
                                <img :src="item.image || '{{ asset('images/placeholder-product.png') }}'"
                                     class="w-12 h-12 rounded border object-cover">

                                <div>
                                    <p class="font-medium" x-text="item.name"></p>
                                    <p class="text-xs text-gray-500" x-text="money(item.price)"></p>
                                    <p class="text-[11px] text-gray-400">
                                        Stock: <span x-text="item.stock"></span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">

                                {{-- - BUTTON --}}
                                <button type="button"
                                        @click="item.quantity > 1 ? item.quantity-- : null"
                                        class="px-2 py-1 bg-gray-200 rounded text-gray-700 hover:bg-gray-300">
                                    -
                                </button>

                                {{-- QUANTITY INPUT --}}
                                <input type="number"
                                       min="1"
                                       :max="item.stock"
                                       x-model.number="item.quantity"
                                       :name="`items[${index}][quantity]`"
                                       class="w-14 text-center border rounded">

                                {{-- + BUTTON --}}
                                <button type="button"
                                        @click="item.quantity < item.stock ? item.quantity++ : null"
                                        class="px-2 py-1 bg-gray-200 rounded text-gray-700 hover:bg-gray-300">
                                    +
                                </button>

                                {{-- HIDDEN FIELDS --}}
                                <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                                <input type="hidden" :name="`items[${index}][unit_price]`" :value="item.price">

                                {{-- LINE TOTAL --}}
                                <span class="font-semibold text-gray-900 min-w-[80px] text-right"
                                      x-text="money(item.quantity * item.price)">
                                </span>

                                {{-- REMOVE --}}
                                <button type="button"
                                        @click="remove(index)"
                                        class="text-red-500 hover:text-red-700 px-2">
                                    ×
                                </button>

                            </div>

                        </div>
                    </template>

                </div>

                {{-- SUMMARY --}}
                <div class="border-t mt-6 pt-4">
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total:</span>
                        <span x-text="money(subtotal())"></span>
                    </div>
                </div>

                {{-- SUBMIT --}}
                <button type="submit"
                        class="mt-6 w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Save Transaction
                </button>

            </div>

        </div>

    </form>

</div>

</x-staff-layout>
