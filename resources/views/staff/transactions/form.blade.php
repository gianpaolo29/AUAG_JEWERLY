<x-staff-layout title="New Transaction">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
     x-data="{
        search: '',
        products: @js($products),
        items: [],

        // Customer data
        customerTab: 'existing', // 'new' or 'existing'
        customerSearch: '',
        customers: @js($customers),
        selectedCustomerId: null,
        selectedCustomerName: '',

        // Error Modal State (SweetAlert style)
        errorOpen: false,
        errorMessage: '',

        init() {
            // Trigger modal if server-side validation fails
            @if($errors->any())
                this.showError('{{ $errors->first() }}');
            @endif
        },

        showError(msg) {
            this.errorMessage = msg;
            this.errorOpen = true;
        },

        filtered() {
            if (!this.search) return this.products;
            const s = this.search.toLowerCase();
            return this.products.filter(p =>
                (p.name || '').toLowerCase().includes(s)
            );
        },

        filteredCustomers() {
            if (!this.customerSearch) return this.customers;
            const s = this.customerSearch.toLowerCase();
            return this.customers.filter(c =>
                (c.name || '').toLowerCase().includes(s)
                || (c.email || '').toLowerCase().includes(s)
                || (c.phone || '').toLowerCase().includes(s)
            );
        },

        selectCustomer(c) {
            this.selectedCustomerId = c.id;
            this.selectedCustomerName = c.name;
            this.customerSearch = c.name; // Fill input with name
        },

        add(p) {
            const max = Number(p.stock || 0);
            
            // Validation: Out of stock
            if (max <= 0) {
                this.showError('This item is currently out of stock.');
                return;
            }

            let found = this.items.find(i => i.product_id === p.id);

            if (found) {
                if (found.quantity < max) {
                    found.quantity++;
                } else {
                    this.showError('Cannot add more. Max stock reached.');
                }
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
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 border-b border-gray-200 pb-6">
        <div>
            <h1 class="text-3xl font-serif font-bold text-gray-900 tracking-tight">Record Sale</h1>
            <p class="text-sm text-gray-500 mt-1">Create a new transaction record</p>
        </div>
        <a href="{{ route('staff.transactions.index') }}"
           class="mt-4 sm:mt-0 px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition shadow-sm">
            ← Back to List
        </a>
    </div>

    {{-- FORM --}}
    <form action="{{ route('staff.transactions.store') }}" method="POST">
        @csrf

        {{-- CUSTOMER SECTION --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.05)] border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-serif font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-2">Customer Details</h2>

            {{-- Tabs --}}
            <div class="flex gap-4 mb-6">
                <button type="button"
                        @click="customerTab = 'existing'"
                        class="px-5 py-2 text-sm font-medium rounded-full transition-all border"
                        :class="customerTab === 'existing'
                            ? 'bg-gray-900 text-white border-gray-900 shadow-md'
                            : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'">
                    Select Existing
                </button>
                <button type="button"
                        @click="customerTab = 'new'"
                        class="px-5 py-2 text-sm font-medium rounded-full transition-all border"
                        :class="customerTab === 'new'
                            ? 'bg-gray-900 text-white border-gray-900 shadow-md'
                            : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'">
                    Register New
                </button>
            </div>

            <input type="hidden" name="customer_mode" :value="customerTab">

            {{-- EXISTING CUSTOMER SEARCH --}}
            <div x-show="customerTab === 'existing'" x-transition>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Find Customer</label>
                <div class="relative">
                    <input type="text"
                           x-model="customerSearch"
                           placeholder="Type to search name..."
                           class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-yellow-500 focus:border-yellow-500 block p-3.5 shadow-sm">
                    
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                {{-- Dropdown Results --}}
                <div x-show="customerSearch.length > 0 && selectedCustomerName !== customerSearch" 
                     class="mt-2 max-h-56 overflow-y-auto border border-gray-100 rounded-xl bg-white shadow-xl z-10">
                    <template x-for="c in filteredCustomers()" :key="c.id">
                        <button type="button"
                                @click="selectCustomer(c)"
                                class="w-full text-left px-4 py-3 flex flex-col hover:bg-yellow-50 border-b border-gray-50 last:border-0 transition">
                            <span class="text-sm font-bold text-gray-800" x-text="c.name"></span>
                            <div class="flex gap-2 text-xs text-gray-500 mt-0.5">
                                <span x-text="c.email"></span> • <span x-text="c.phone"></span>
                            </div>
                        </button>
                    </template>
                    <template x-if="filteredCustomers().length === 0">
                        <div class="p-4 text-sm text-gray-500 text-center">No customers found.</div>
                    </template>
                </div>

                <input type="hidden" name="customer_id" :value="selectedCustomerId">
            </div>

            {{-- NEW CUSTOMER FORM --}}
            <div x-show="customerTab === 'new'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                           class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Email</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                           class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Phone</label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                           class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- LEFT: PRODUCT SELECTOR --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.05)] border border-gray-100 p-6">
                
                <h2 class="text-lg font-serif font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Select Products
                </h2>

                <input type="text"
                       x-model="search"
                       placeholder="Search product inventory..."
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl mb-4 focus:ring-yellow-500 focus:border-yellow-500 shadow-inner">

                <div class="max-h-[500px] overflow-y-auto pr-1 space-y-2 custom-scrollbar">
                    <template x-for="p in filtered()" :key="p.id">
                        <button type="button"
                                @click="add(p)"
                                class="w-full group flex items-center justify-between border border-gray-100 rounded-xl p-3 hover:border-yellow-400 hover:bg-yellow-50/30 hover:shadow-md transition-all duration-200">

                            <div class="flex items-center gap-4">
                                <div class="h-14 w-14 rounded-lg overflow-hidden border border-gray-200 bg-white">
                                    <img :src="p.image_url || '{{ asset('images/placeholder-product.png') }}'"
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="text-left">
                                    <p class="font-bold text-gray-800 group-hover:text-gray-900" x-text="p.name"></p>
                                    <p class="text-xs text-yellow-600 font-bold" x-text="money(p.price)"></p>
                                    <p class="text-[11px] text-gray-400 mt-1">Available: <span x-text="p.stock"></span></p>
                                </div>
                            </div>

                            <span class="px-4 py-2 bg-gray-900 text-white text-xs font-bold rounded-lg shadow group-hover:bg-yellow-500 transition">
                                Add +
                            </span>
                        </button>
                    </template>
                    <template x-if="filtered().length === 0">
                        <div class="text-center py-10 text-gray-400 text-sm">No products found.</div>
                    </template>
                </div>
            </div>

            {{-- RIGHT: CART --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-gray-100 p-6 sticky top-24">
                    
                    <h2 class="text-lg font-serif font-semibold text-gray-800 mb-6 flex justify-between items-center">
                        Current Cart
                        <span class="text-xs font-sans bg-gray-100 text-gray-600 px-2 py-1 rounded-full" x-text="items.length + ' Items'"></span>
                    </h2>

                    <template x-if="items.length === 0">
                        <div class="text-center py-12 border-2 border-dashed border-gray-200 rounded-xl bg-gray-50">
                            <p class="text-gray-400 text-sm font-medium">Cart is empty</p>
                            <p class="text-xs text-gray-300 mt-1">Select items from the left</p>
                        </div>
                    </template>

                    {{-- Cart Items --}}
                    <div class="space-y-4 max-h-[350px] overflow-y-auto pr-1 mb-6">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-3 border border-gray-100 rounded-xl bg-gray-50/50 flex flex-col gap-3 relative group">
                                
                                {{-- Remove Button --}}
                                <button type="button" @click="remove(index)" class="absolute top-2 right-2 text-gray-300 hover:text-red-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>

                                <div class="flex items-center gap-3">
                                    <img :src="item.image || '{{ asset('images/placeholder-product.png') }}'"
                                         class="w-10 h-10 rounded border object-cover">
                                    <div>
                                        <p class="font-bold text-sm text-gray-800 leading-tight" x-text="item.name"></p>
                                        <p class="text-xs text-gray-500" x-text="money(item.price)"></p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between mt-1">
                                    {{-- QTY Controls --}}
                                    <div class="flex items-center bg-white border border-gray-200 rounded-lg">
                                        <button type="button" @click="item.quantity > 1 ? item.quantity-- : null"
                                                class="px-2 py-1 text-gray-500 hover:bg-gray-100 hover:text-gray-900 rounded-l-lg transition">-</button>
                                        <input type="number" readonly x-model="item.quantity"
                                               class="w-8 text-center text-xs border-none p-0 focus:ring-0 text-gray-800 font-bold">
                                        <button type="button" @click="item.quantity < item.stock ? item.quantity++ : showError('Max stock reached')"
                                                class="px-2 py-1 text-gray-500 hover:bg-gray-100 hover:text-gray-900 rounded-r-lg transition">+</button>
                                    </div>

                                    {{-- Line Total --}}
                                    <span class="font-serif font-bold text-yellow-600 text-sm" x-text="money(item.quantity * item.price)"></span>
                                </div>
                                
                                {{-- Hidden Inputs --}}
                                <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                                <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity">
                                <input type="hidden" :name="`items[${index}][unit_price]`" :value="item.price">
                            </div>
                        </template>
                    </div>

                    {{-- TOTAL & SUBMIT --}}
                    <div class="border-t border-gray-100 pt-4">
                        <div class="flex justify-between items-end mb-6">
                            <span class="text-sm font-medium text-gray-500">Total Amount</span>
                            <span class="text-2xl font-serif font-bold text-gray-900" x-text="money(subtotal())"></span>
                        </div>

                        <button type="submit"
                                :disabled="items.length === 0"
                                class="w-full py-4 bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-xl hover:to-gray-700 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all transform active:scale-[0.98] font-bold tracking-wide">
                            Complete Transaction
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </form>

    {{-- CUSTOM ERROR MODAL (SweetAlert Clone) --}}
    <div x-cloak x-show="errorOpen" 
         class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        {{-- Backdrop --}}
        <div x-show="errorOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                
                {{-- Modal Panel --}}
                <div x-show="errorOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @click.outside="errorOpen = false"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-sm border border-red-100">
                    
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="flex flex-col items-center justify-center text-center">
                            {{-- Animated X Icon --}}
                            <div class="mx-auto flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-red-50 border-4 border-red-50 mb-4 animate-bounce">
                                <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            
                            <h3 class="text-xl font-serif font-bold leading-6 text-gray-900" id="modal-title">
                                Attention Needed
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" x-text="errorMessage"></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse justify-center">
                        <button type="button" 
                                @click="errorOpen = false"
                                class="inline-flex w-full justify-center rounded-xl bg-red-600 px-8 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto transition-colors">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

</x-staff-layout>