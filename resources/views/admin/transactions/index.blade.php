<x-admin-layout title="Transactions">
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
                const n = Number(v || 0);
                return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
         }"
    >

        {{-- HEADER + PRIMARY ACTION --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Transactions</h1>
                <p class="mt-1 text-sm text-gray-500">
                    View and manage all Buy, Pawn, and Repair transactions.
                </p>
            </div>

            <a href="{{ route('admin.transactions.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                New Transaction
            </a>
        </div>

        {{-- FILTERS CARD --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <form method="GET" class="grid gap-4 md:grid-cols-4 items-end">
                {{-- Search --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">
                        Search (Customer / Staff / ID)
                    </label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M15.75 15.75 21 21" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="10.5" cy="10.5" r="6" />
                        </svg>
                        <input type="search" name="q" value="{{ request('q') }}"
                               class="w-full rounded-lg border-gray-300 pl-9 pr-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Search by customer name, sell by, name, or ID">
                    </div>
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">
                        Type
                    </label>
                    <select name="type"
                            class="w-full rounded-lg border-gray-300 py-2 px-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="Buy"   @selected(request('type') === 'Buy')>Buy</option>
                        <option value="Pawn"  @selected(request('type') === 'Pawn')>Pawn</option>
                        <option value="Repair"@selected(request('type') === 'Repair')>Repair</option>
                    </select>
                </div>

                {{-- Date --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">
                        Date
                    </label>
                    <input type="date" name="date"
                           value="{{ request('date') }}"
                           class="w-full rounded-lg border-gray-300 py-2 px-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- Buttons --}}
                <div class="md:col-span-4 flex items-center justify-between pt-2">
                    <p class="text-xs text-gray-500">
                        Showing:
                        <span class="font-semibold text-gray-700">
                            {{ $transactions->total() }} transaction{{ $transactions->total() === 1 ? '' : 's' }}
                        </span>
                    </p>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.transactions.index') }}"
                           class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">
                            Clear
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-1 rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-gray-800">
                            Apply
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">ID</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Customer</th>
                            <th class="px-4 py-3 text-left">Sell By</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse($transactions as $t)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-4 py-3 font-mono text-xs text-gray-500">
                                    #{{ $t->id }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800">
                                    {{ $t->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $t->customer?->name ?? 'Walk-in Customer' }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $t->customer?->email ?? '' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col">
                                        <span class="text-sm text-gray-800">
                                            {{ $t->staff?->name ?? '—' }}
                                        </span>
                                        @if($t->staff?->email)
                                            <span class="text-xs text-gray-400">
                                                {{ $t->staff->email }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $typeColor = match($t->type) {
                                            'Buy'    => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                            'Pawn'   => 'bg-amber-50 text-amber-700 border-amber-100',
                                            'Repair' => 'bg-sky-50 text-sky-700 border-sky-100',
                                            default  => 'bg-gray-50 text-gray-700 border-gray-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $typeColor }}">
                                        {{ $t->type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 whitespace-nowrap">
                                    ₱{{ number_format($t->total_amount ?? 0, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- VIEW: open modal --}}
                                        <button type="button"
                                            @click="openDetails(@js([
                                                'id' => $t->id,
                                                'datetime' => $t->created_at->format('M d, Y h:i A'),
                                                'type' => $t->type,
                                                'customer_name' => $t->customer->name ?? 'Walk-in Customer',
                                                'customer_email' => $t->customer->email ?? '',
                                                'staff_name' => $t->staff->name ?? '—',
                                                'staff_email' => $t->staff->email ?? '',
                                                'items' => $t->items->map(function ($item) {
                                                    $placeholder = asset('images/placeholder-product.png');
                                                    $name = 'Item #'.$item->id;
                                                    $imageUrl = $placeholder;

                                                    // 1) PRODUCT (Buy)
                                                    if ($item->product) {
                                                        $product = $item->product;
                                                        $name = $product->name ?? ('Product #'.$item->product_id);

                                                        $imageUrl = $product->image_url
                                                            ?? ($product->picture ? asset('storage/'.$product->picture->url) : $placeholder);
                                                    }
                                                    // 2) PAWN ITEM
                                                    elseif ($item->pawnItem) {
                                                        $pawn = $item->pawnItem;
                                                        $name = 'Pawn: '.($pawn->title ?? ('Pawn #'.$item->pawn_item_id));

                                                        $firstPic = $pawn->pictures->first();
                                                        if ($firstPic) {
                                                            $imageUrl = asset('storage/'.$firstPic->url);
                                                        }
                                                    }
                                                    // 3) REPAIR
                                                    elseif ($item->repair) {
                                                        $repair = $item->repair;
                                                        $name = 'Repair: '.(strlen($repair->description ?? '') > 40
                                                            ? substr($repair->description, 0, 40).'...'
                                                            : ($repair->description ?? 'Repair #'.$item->repair_id));

                                                        if ($repair->picture) {
                                                            $imageUrl = asset('storage/'.$repair->picture->url);
                                                        }
                                                    }

                                                    return [
                                                        'product_name' => $name,
                                                        'image_url'    => $imageUrl,
                                                        'quantity'     => $item->quantity,
                                                        'unit_price'   => $item->unit_price,
                                                        'line_total'   => $item->line_total,
                                                    ];
                                                }),

                                                'total' => $t->items->sum('line_total'),
                                            ]))"
                                            class="inline-flex items-center rounded-md p-1.5 text-xs text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M2.25 12s3-6.75 9.75-6.75S21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z" stroke-linecap="round" stroke-linejoin="round" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </button>

                                        {{-- (optional) delete button here --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500 bg-gray-50">
                                    No transactions found. Create a new one to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-gray-200 px-4 py-3 flex items-center justify-between">
                <p class="text-xs text-gray-500">
                    Showing
                    <span class="font-medium text-gray-700">
                        {{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }}
                    </span>
                    of
                    <span class="font-medium text-gray-700">
                        {{ $transactions->total() }}
                    </span>
                    results
                </p>
                <div>
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>

        {{-- 🔍 TRANSACTION DETAILS MODAL (phone size) --}}
        <div
            x-cloak
            x-show="showDetails"
            @click.self="closeDetails()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        >
            <div class="w-full max-w-xs sm:max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden">
                {{-- Header --}}
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400">Transaction</p>
                        <p class="text-sm font-semibold text-gray-900">
                            #<span x-text="details.id"></span>
                        </p>
                    </div>
                    <button @click="closeDetails()" class="p-1.5 rounded-full hover:bg-gray-100 text-gray-500">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-4 py-3 space-y-3 max-h-[70vh] overflow-y-auto text-sm">
                    {{-- Basic info --}}
                    <div class="space-y-1">
                        <p class="text-[11px] text-gray-400" x-text="details.datetime"></p>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                <span x-text="details.type"></span>
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2 border-t border-gray-100 pt-2">
                        <div>
                            <p class="text-[11px] text-gray-400">Customer</p>
                            <p class="text-sm font-medium text-gray-900" x-text="details.customer_name"></p>
                            <p class="text-[11px] text-gray-400" x-text="details.customer_email"></p>
                        </div>
                        <div>
                            <p class="text-[11px] text-gray-400">Processed By</p>
                            <p class="text-sm font-medium text-gray-900" x-text="details.staff_name"></p>
                            <p class="text-[11px] text-gray-400" x-text="details.staff_email"></p>
                        </div>
                    </div>

                    {{-- Items --}}
                    <div class="border-t border-gray-100 pt-3 space-y-2">
                        <p class="text-xs font-semibold text-gray-700">Items</p>

                        <template x-for="(item, idx) in details.items" :key="idx">
                            <div class="flex items-center justify-between gap-2 rounded-lg border border-gray-100 bg-gray-50 px-2 py-2">
                                <div class="flex items-center gap-2">
                                    <img :src="item.image_url"
                                         class="w-9 h-9 rounded-md object-cover border border-gray-200"
                                         alt="">
                                    <div>
                                        <p class="text-xs font-medium text-gray-900" x-text="item.product_name"></p>
                                        <p class="text-[11px] text-gray-500"
                                           x-text="item.quantity + ' × ' + money(item.unit_price)">
                                        </p>
                                    </div>
                                </div>
                                <p class="text-xs font-semibold text-gray-900 text-right"
                                   x-text="money(item.line_total)">
                                </p>
                            </div>
                        </template>

                        <template x-if="!details.items || details.items.length === 0">
                            <p class="text-[11px] text-gray-400">No items found.</p>
                        </template>
                    </div>
                </div>

                {{-- Footer total --}}
                <div class="px-4 py-3 border-t bg-gray-50 flex items-center justify-between text-sm">
                    <span class="font-medium text-gray-700">Total</span>
                    <span class="font-semibold text-gray-900" x-text="money(details.total)"></span>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
