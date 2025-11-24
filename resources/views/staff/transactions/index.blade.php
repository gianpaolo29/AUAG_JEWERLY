<x-staff-layout title="Transactions">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex flex-col gap-6"
            x-data="{
                showDetails: false,
                details: {
                    id: null,
                    datetime: '',
                    type: '',
                    customer_name: '',
                    customer_email: '',
                    staff_name: '',
                    staff_email: '',
                    items: [],
                    total: 0,
                },
                openDetails(data) {
                    this.details = data;
                    this.showDetails = true;
                },
                closeDetails() {
                    this.showDetails = false;
                },
                money(v) {
                    return '₱' + Number(v || 0).toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },
                getTypeColor(type) {
                    switch(type) {
                        case 'Buy': return 'bg-green-50 text-green-700 border-green-200';
                        case 'Pawn': return 'bg-yellow-50 text-yellow-700 border-yellow-200';
                        case 'Repair': return 'bg-blue-50 text-blue-700 border-blue-200';
                        default: return 'bg-gray-50 text-gray-700 border-gray-200';
                    }
                }
            }">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-b pb-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">My Transactions 📊</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        All transactions you processed.
                    </p>
                </div>

                <a href="{{ route('staff.transactions.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-indigo-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 5v14M5 12h14" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    New Transaction
                </a>
            </div>

            {{-- FILTERS --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <form method="GET" class="grid gap-4 md:grid-cols-4 lg:grid-cols-5 items-end">

                    {{-- Search --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Search Transaction ID
                        </label>
                        <input type="search" name="q" value="{{ request('q') }}"
                           class="w-full rounded-xl border-gray-300 pl-3 pr-4 py-2.5 text-sm shadow-sm focus:border-indigo-500"
                           placeholder="Enter ID e.g. 104">
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="date" value="{{ request('date') }}"
                               class="w-full rounded-xl border-gray-300 py-2.5 px-3 text-sm shadow-sm">
                    </div>

                    {{-- Buttons --}}
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <a href="{{ route('staff.transactions.index') }}"
                           class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2.5 text-sm text-gray-700">
                            Clear
                        </a>

                        <button type="submit"
                           class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm text-white shadow-md hover:bg-indigo-700">
                            Apply
                        </button>
                    </div>

                    {{-- Count --}}
                    <p class="md:col-span-4 lg:col-span-5 text-sm text-gray-500 pt-2">
                        Showing <strong>{{ $transactions->total() }}</strong> results
                    </p>

                </form>
            </div>

            {{-- TABLE --}}
            <div class="bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left">ID</th>
                                <th class="px-6 py-3 text-left">Date</th>
                                <th class="px-6 py-3 text-left">Type</th>
                                <th class="px-6 py-3 text-right">Total</th>
                                <th class="px-6 py-3 text-center">View</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse($transactions as $t)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-mono text-xs text-gray-600">#{{ $t->id }}</td>

                                    <td class="px-6 py-3">
                                        {{ $t->created_at->format('M d, Y') }}
                                        <div class="text-xs text-gray-400">{{ $t->created_at->format('h:i A') }}</div>
                                    </td>


                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                                              :class="getTypeColor('{{ $t->type }}')">
                                            {{ $t->type }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-3 text-right font-bold">
                                        {{ number_format($t->total_amount, 2) }}
                                    </td>

                                    <td class="px-6 py-3 text-center">
                                        <button @click="openDetails(@js([
                                            'id' => $t->id,
                                            'datetime' => $t->created_at->format('M d, Y h:i A'),
                                            'type' => $t->type,
                                            'customer_name' => $t->customer?->name ?? 'Walk-in Customer',
                                            'customer_email' => $t->customer?->email ?? '',
                                            'staff_name' => $t->staff?->name ?? '',
                                            'staff_email' => $t->staff?->email ?? '',
                                            'items' => $t->items->map(fn ($i) => [
                                                'product_name' => $i->product->name ?? 'Item',
                                                'image_url' => $i->product->image_url ?? asset('images/placeholder-product.png'),
                                                'quantity' => $i->quantity,
                                                'unit_price' => $i->unit_price,
                                                'line_total' => $i->line_total,
                                            ]),
                                            'total' => $t->items->sum('line_total'),
                                        ]))"
                                            class="p-2 rounded hover:bg-indigo-50 text-indigo-600">
                                            👁️
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-gray-500">
                                        No transactions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="border-t px-4 py-4 flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Showing
                        {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }}
                        of {{ $transactions->total() }}
                    </p>

                    <div class="hidden sm:block">
                        {{ $transactions->links() }}
                    </div>
                </div>

            </div>

            {{-- MODAL --}}
            <div x-cloak x-show="showDetails" @click.self="closeDetails()" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">

                <div class="w-full max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden"
                     x-transition>

                    {{-- Header --}}
                    <div class="px-5 py-4 border-b bg-gray-50 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500">Transaction ID</p>
                            <p class="text-lg font-bold">#<span x-text="details.id"></span></p>
                        </div>

                        <button @click="closeDetails()" class="p-2 hover:bg-gray-100 rounded-full">
                            ✕
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-5 py-4 space-y-4 max-h-[70vh] overflow-y-auto text-sm">

                        <div class="pb-2 border-b">
                            <span class="text-xs text-gray-500" x-text="details.datetime"></span>
                        </div>

                        <div class="border-b pb-3 grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs font-semibold">Customer</p>
                                <p class="font-medium" x-text="details.customer_name"></p>
                            </div>
                            <div>

                                <p class="text-xs font-semibold">Processed By</p>
                                <p class="font-medium" x-text="details.staff_name"></p>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-semibold">Items</p>

                            <template x-for="item in details.items">
                                <div class="flex justify-between gap-3 p-3 border rounded-lg">
                                    <div class="flex gap-3">
                                        <img :src="item.image_url" class="w-10 h-10 rounded object-cover border">
                                        <div>
                                            <p class="font-medium" x-text="item.product_name"></p>
                                            <p class="text-xs text-gray-500"
                                               x-text="item.quantity + ' × ' + money(item.unit_price)"></p>
                                        </div>
                                    </div>
                                    <p class="font-semibold" x-text="money(item.line_total)"></p>
                                </div>
                            </template>
                        </div>

                    </div>

                    <div class="bg-gray-100 px-5 py-3 flex justify-between font-bold">
                        <span>GRAND TOTAL</span>
                        <span x-text="money(details.total)"></span>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-staff-layout>
