<x-staff-layout title="Pawn Items">

@if (session('download_pawn_id'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const url = "{{ route('staff.pawn.download', ['pawn' => session('download_pawn_id')]) }}";
            const link = document.createElement('a');
            link.href = url;
            document.body.appendChild(link);
            link.click();
            link.remove();
        });
    </script>
@endif

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="pawnIndex()">

    <div class="flex flex-col gap-6">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                    Pawn Items
                </h1>
                <p class="mt-1 text-base text-gray-500">
                    All pawned items you have recorded.
                </p>
            </div>

            <a href="{{ route('staff.pawn.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-yellow-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-yellow-700 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                New Pawn Item
            </a>
        </div>

        {{-- FILTERS --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <form method="GET" class="grid gap-x-6 gap-y-4 md:grid-cols-4 lg:grid-cols-8 items-end">

                <div class="md:col-span-2 lg:col-span-3">
                    <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                             fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <circle cx="10.5" cy="10.5" r="6"/>
                            <path d="M15.75 15.75 21 21" stroke-linecap="round"/>
                        </svg>
                        <input type="text" id="q" name="q" value="{{ request('q') }}"
                               class="w-full rounded-xl border-gray-300 pl-10 pr-4 py-2.5 text-sm shadow-sm focus:ring-yellow-500 focus:border-yellow-500"
                               placeholder="Customer or item title">
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status"
                            class="w-full rounded-xl border-gray-300 py-2.5 px-3 text-sm shadow-sm focus:ring-yellow-500 focus:border-yellow-500">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(request('status')=='active')>Active</option>
                        <option value="redeemed" @selected(request('status')=='redeemed')>Redeemed</option>
                        <option value="forfeited" @selected(request('status')=='forfeited')>Forfeited</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                    <input type="date" id="due_date" name="due_date" value="{{ request('due_date') }}"
                           class="w-full rounded-xl border-gray-300 py-2.5 px-3 text-sm shadow-sm focus:ring-yellow-500 focus:border-yellow-500">
                </div>

                <div class="md:col-span-4 lg:col-span-1 flex items-end justify-end gap-2">
                    <a href="{{ route('staff.pawn.index') }}"
                       class="flex-1 text-center sm:flex-none px-4 py-2.5 rounded-xl border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 transition">
                        Clear
                    </a>

                    <button type="submit"
                            class="flex-1 text-center sm:flex-none px-4 py-2.5 rounded-xl bg-yellow-600 text-white text-sm shadow hover:bg-yellow-700 transition">
                        Apply
                    </button>
                </div>
            </form>

            <div class="border-t border-gray-200 mt-6 pt-4">
                <p class="text-sm text-gray-500">
                    Showing <strong>{{ $pawnItems->total() }}</strong> record{{ $pawnItems->total() != 1 ? 's' : '' }}.
                </p>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-600">
                    <tr>
                        <th class="px-6 py-3 text-left">Customer / Item</th>
                        <th class="px-6 py-3 text-right">Principal</th>
                        <th class="px-6 py-3 text-right">Interest</th>
                        <th class="px-6 py-3 text-right">Total Due</th>
                        <th class="px-6 py-3 text-left">Due Date</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-center">Receipt</th>
                        <th class="px-6 py-3 text-center">Redeem</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($pawnItems as $item)
                        <tr class="hover:bg-yellow-50/50 transition @if($item->is_overdue) border-l-4 border-rose-500 @endif">
                            <td class="px-6 py-3">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-gray-900">
                                        {{ $item->customer?->name ?? 'Walk-in' }}
                                    </span>
                                    <span class="text-xs text-gray-500 mt-0.5">{{ $item->title }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-3 text-right text-gray-900 font-medium">
                                ₱{{ number_format($item->price, 2) }}
                            </td>

                            <td class="px-6 py-3 text-right text-orange-600 font-medium">
                                ₱{{ number_format($item->computed_interest, 2) }}
                            </td>

                            <td class="px-6 py-3 text-right font-bold text-green-700">
                                ₱{{ number_format($item->to_pay, 2) }}
                            </td>

                            <td class="px-6 py-3 whitespace-nowrap">
                                <span class="@if($item->is_overdue) text-rose-600 font-semibold @endif">
                                    {{ $item->due_date?->format('M d, Y') ?? '—' }}
                                </span>
                            </td>

                            <td class="px-6 py-3 text-center">
                                @php
                                    $badge = match($item->status) {
                                        'active'    => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                        'redeemed'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'forfeited' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default     => 'bg-gray-50 text-gray-700 border-gray-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 text-xs rounded-full border font-semibold uppercase {{ $badge }}">
                                    {{ $item->status }}
                                </span>
                            </td>

                            {{-- RECEIPT --}}
                            <td class="px-6 py-3 text-center">
                                <button type="button"
                                        class="px-3 py-1.5 text-xs rounded-lg bg-gray-900 text-white hover:bg-gray-800 transition"
                                        @click="openReceipt(@js([
                                        'id' => $item->id,
                                        'customer' => $item->customer?->name ?? 'Walk-in',
                                        'title' => $item->title,
                                        'description' => $item->description,

                                        // Pawn created date (created_at)
                                        'created_at' => optional($item->created_at)->format('M d, Y'),

                                        // Loan date (your actual loan_date column)
                                        'loan_date' => optional($item->loan_date)->format('M d, Y'),

                                        'due_date' => optional($item->due_date)->format('M d, Y'),
                                        'principal' => number_format($item->price, 2),
                                        'interest' => number_format($item->computed_interest, 2),
                                        'total' => number_format($item->to_pay, 2),
                                        'status' => $item->status,
                                        'download_url' => route('staff.pawn.download', ['pawn' => $item->id]),
                                    ]))">
                                    View
                                </button>
                            </td>

                            {{-- REDEEM --}}
                            <td class="px-6 py-3 text-center">
                                @if($item->status === 'redeemed')
                                    <button disabled
                                            class="px-3 py-1.5 text-xs rounded-lg bg-emerald-100 text-emerald-700 cursor-not-allowed">
                                        Redeemed
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('staff.pawn.redeem', ['pawn' => $item->id]) }}">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition">
                                            Redeem
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500 bg-gray-50">
                                No pawn items found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="border-t border-gray-200 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-500">
                    Showing
                    <strong>{{ $pawnItems->firstItem() }}</strong>–<strong>{{ $pawnItems->lastItem() }}</strong>
                    of <strong>{{ $pawnItems->total() }}</strong> records
                </p>

                <div>
                    {{ $pawnItems->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- RECEIPT MODAL --}}
    <div x-cloak x-show="receiptOpen" class="fixed inset-0 z-[100]">
        <div class="absolute inset-0 bg-black/60" @click="closeReceipt()"></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Pawn Receipt Preview</h3>
                        <p class="text-xs text-gray-500" x-text="receipt ? ('Pawn #' + receipt.id) : ''"></p>
                    </div>
                    <button class="p-2 rounded-lg hover:bg-gray-100" @click="closeReceipt()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Customer</span><span class="font-semibold" x-text="receipt?.customer"></span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Item</span><span class="font-semibold" x-text="receipt?.title"></span></div>
                    <div class="text-gray-600" x-text="receipt?.description"></div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="rounded-xl bg-gray-50 p-3">
                            <div class="text-xs text-gray-500">Loan Date</div>
                            <div class="font-semibold" x-text="receipt?.created_at"></div>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <div class="text-xs text-gray-500">Due Date</div>
                            <div class="font-semibold" x-text="receipt?.due_date"></div>
                        </div>
                    </div>

                    <div class="border-t pt-3 space-y-2">
                        <div class="flex justify-between"><span class="text-gray-500">Principal</span><span class="font-semibold" x-text="'₱' + receipt?.principal"></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Interest</span><span class="font-semibold text-orange-600" x-text="'₱' + receipt?.interest"></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Total Due</span><span class="font-bold text-green-700" x-text="'₱' + receipt?.total"></span></div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t bg-gray-50 flex gap-2">
                    <button class="flex-1 px-4 py-2.5 rounded-xl bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800"
                            @click="downloadReceipt(receipt?.download_url)">
                        Download PDF
                    </button>
                    <button class="px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold hover:bg-white"
                            @click="closeReceipt()">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function pawnIndex() {
        return {
            receiptOpen: false,
            receipt: null,

            openReceipt(payload) {
                this.receipt = payload;
                this.receiptOpen = true;
            },

            closeReceipt() {
                this.receiptOpen = false;
                this.receipt = null;
            },

            downloadReceipt(url) {
                if (!url) return;
                const a = document.createElement('a');
                a.href = url;
                document.body.appendChild(a);
                a.click();
                a.remove();
            },
        };
    }
</script>

</x-staff-layout>
