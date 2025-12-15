<x-staff-layout title="Transactions History">

    @if (session('download_transaction_id'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const url = "{{ route('staff.transactions.download', ['transaction' => session('download_transaction_id')]) }}";

                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', '');
                document.body.appendChild(link);
                link.click();
                link.remove();
            });
        </script>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <div class="flex flex-col gap-8"
             x-data="transactionsHistory()">

            {{-- HEADER --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200 pb-6">
                <div>
                    <h1 class="text-3xl font-serif font-bold text-gray-900 tracking-tight">Transaction History</h1>
                    <p class="text-sm text-gray-500 mt-1">View and manage past sales and services.</p>
                </div>

                <a href="{{ route('staff.transactions.create') }}"
                   class="mt-4 sm:mt-0 inline-flex items-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-gray-800 transition transform active:scale-95">
                    <svg class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Sale
                </a>
            </div>

            {{-- FILTERS --}}
            <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.05)] border border-gray-100 p-6">
                <form method="GET" class="grid gap-6 md:grid-cols-4 lg:grid-cols-5 items-end">

                    {{-- Search --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Search Transaction</label>
                        <div class="relative">
                            <input type="search" name="q" value="{{ request('q') }}"
                                   class="w-full rounded-xl border-gray-200 bg-gray-50 pl-10 pr-4 py-2.5 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500 transition-colors"
                                   placeholder="Transaction ID (e.g. 104)">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Filter Date</label>
                        <input type="date" name="date" value="{{ request('date') }}"
                               class="w-full rounded-xl border-gray-200 bg-gray-50 py-2.5 px-3 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                    </div>

                    {{-- Buttons --}}
                    <div class="flex items-center gap-2 md:col-span-1 lg:col-span-2 justify-end">
                        <a href="{{ route('staff.transactions.index') }}"
                           class="inline-flex items-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                            Reset
                        </a>

                        <button type="submit"
                                class="inline-flex items-center rounded-xl bg-yellow-500 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-yellow-600 transition">
                            Apply
                        </button>
                    </div>

                </form>
            </div>

            {{-- TABLE --}}
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Transaction ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total Amount</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-50 bg-white">
                        @forelse($transactions as $t)
                            <tr class="hover:bg-yellow-50/20 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm font-medium text-gray-900">
                                        #{{ str_pad($t->id, 6, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $t->created_at->format('M d, Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $t->created_at->format('h:i A') }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold shadow-sm"
                                          :class="getTypeColor('{{ $t->type }}')">
                                        {{ $t->type }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-serif font-bold text-gray-900">
                                        {{ number_format($t->total_amount, 2) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button
                                        @click="openDetails(@js([
                                            'id' => str_pad($t->id, 6, '0', STR_PAD_LEFT),
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
                                            'download_url' => route('staff.transactions.download', $t),  // <-- ADD THIS
                                        ]))"
                                        class="inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <p class="text-sm font-medium">No transactions found.</p>
                                        <p class="text-xs mt-1">Try adjusting your search filters.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="border-t border-gray-100 px-6 py-4 bg-gray-50/50">
                    {{ $transactions->links() }}
                </div>
            </div>

            {{-- MODAL – RECEIPT PREVIEW --}}
            <div x-cloak x-show="showDetails"
                 class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">

                {{-- Backdrop --}}
                <div x-show="showDetails"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
                     @click="closeDetails()"></div>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                        {{-- Modal Panel --}}
                        <div x-show="showDetails"
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-200">

                            {{-- Modal Header --}}
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                                <div>
                                    <h3 class="text-lg font-serif font-bold text-gray-900">Receipt Preview</h3>
                                    <p class="text-xs text-gray-500">
                                        Transaction #<span x-text="details.id"></span>
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="printReceipt()"
                                            class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-xs font-bold text-white hover:bg-gray-800 transition shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                        Print
                                    </button>
                                    <button @click="closeDetails()"
                                            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- RECEIPT CONTENT PREVIEW (simple, not full print design) --}}
                            <div class="p-8 bg-white" x-ref="receiptContent">

                                {{-- Receipt Header --}}
                                <div class="text-center border-b-2 border-yellow-500 pb-6 mb-6">
                                    <h2 class="text-2xl font-serif font-bold text-gray-900 tracking-wider">
                                        AUAG JEWELRY
                                    </h2>
                                    <p class="text-xs text-gray-500 mt-1 uppercase tracking-wide">Official Receipt</p>
                                    <div class="mt-4 flex justify-between text-xs text-gray-600">
                                        <span>No: <strong x-text="details.id"></strong></span>
                                        <span>Date: <span x-text="details.datetime"></span></span>
                                    </div>
                                </div>

                                {{-- Info Grid --}}
                                <div class="grid grid-cols-2 gap-8 mb-8 text-xs">
                                    <div>
                                        <p class="font-bold text-yellow-600 uppercase border-b border-gray-100 pb-1 mb-2">
                                            Customer
                                        </p>
                                        <p class="font-bold text-gray-900 text-sm" x-text="details.customer_name"></p>
                                        <p class="text-gray-500" x-text="details.customer_email"></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-yellow-600 uppercase border-b border-gray-100 pb-1 mb-2">
                                            Details
                                        </p>
                                        <p>Type:
                                            <span class="font-bold text-gray-900" x-text="details.type"></span>
                                        </p>
                                        <p>Cashier: <span x-text="details.staff_name"></span></p>
                                    </div>
                                </div>

                                {{-- Items Table --}}
                                <table class="w-full text-xs mb-6">
                                    <thead>
                                    <tr class="bg-gray-900 text-white">
                                        <th class="py-2 px-3 text-left uppercase">Description</th>
                                        <th class="py-2 px-3 text-center uppercase">Qty</th>
                                        <th class="py-2 px-3 text-right uppercase">Price</th>
                                        <th class="py-2 px-3 text-right uppercase">Total</th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                    <template x-for="item in details.items" :key="item.product_name">
                                        <tr>
                                            <td class="py-3 px-3">
                                                <span class="font-bold text-gray-900"
                                                      x-text="item.product_name"></span>
                                            </td>
                                            <td class="py-3 px-3 text-center" x-text="item.quantity"></td>
                                            <td class="py-3 px-3 text-right" x-text="money(item.unit_price)"></td>
                                            <td class="py-3 px-3 text-right font-bold text-gray-900"
                                                x-text="money(item.line_total)"></td>
                                        </tr>
                                    </template>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="3"
                                            class="pt-4 text-right text-sm font-bold text-gray-900 border-t-2 border-yellow-500">
                                            TOTAL DUE
                                        </td>
                                        <td class="pt-4 text-right text-lg font-serif font-bold text-yellow-600 border-t-2 border-yellow-500"
                                            x-text="money(details.total)"></td>
                                    </tr>
                                    </tfoot>
                                </table>

                                {{-- Footer Note --}}
                                <div class="text-center text-[10px] text-gray-400 mt-10">
                                    <p class="font-bold text-gray-500">Thank you for your purchase!</p>
                                    <p class="mt-1">Items may be exchanged within 7 days with this receipt.</p>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ALPINE JS CONTROLLER --}}
    <script>
        function transactionsHistory() {
            return {
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
                    receipt_url: null,
                    download_url: null,   
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
                        maximumFractionDigits: 2,
                    });
                },
                getTypeColor(type) {
                    switch (type) {
                        case 'Buy':
                            return 'bg-green-500/10 text-green-600 border-green-500/20';
                        case 'Pawn':
                            return 'bg-yellow-500/10 text-yellow-600 border-yellow-500/20';
                        case 'Repair':
                            return 'bg-blue-500/10 text-blue-600 border-blue-500/20';
                        default:
                            return 'bg-gray-100 text-gray-600 border-gray-200';
                    }
                },
                printReceipt() {
                    if (!this.details.download_url) return;

                    const link = document.createElement('a');
                    link.href = this.details.download_url;
                    link.setAttribute('download', '');
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },

            };
        }
    </script>
</x-staff-layout>
