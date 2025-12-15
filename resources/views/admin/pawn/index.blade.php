<x-admin-layout title="Pawn Items">

    {{-- Auto-download receipt after create/update --}}
    @if (session('download_pawn_id'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const url = @json(route('admin.pawn.download', session('download_pawn_id')));
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = url;
                document.body.appendChild(iframe);
            });
        </script>
    @endif

    {{-- Auto download after redeem (if you already have admin.transactions.download) --}}
    @if (session('download_transaction_id'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const url = "{{ route('admin.transactions.download', ['transaction' => session('download_transaction_id')]) }}";
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = url;
                document.body.appendChild(iframe);
            });
        </script>
    @endif

    <div class="w-full px-4 sm:px-6 py-6" x-data="pawnAdminIndex()">

        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div class="space-y-1">
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-dark-900 tracking-tight">Pawn Items</h1>
                <p class="text-sm text-gray-600">Manage pawn tickets, compute penalties, and redeem items</p>
            </div>

            <a href="{{ route('admin.pawn.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-yellow-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-800 transition">
                <svg class="h-4 w-4 text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                New Pawn Item
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.05)] border border-gray-100 p-4 sm:p-5 mb-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Search</label>
                        <input type="search" name="q" value="{{ request('q') }}"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl bg-gray-50 text-sm
                                      focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                               placeholder="Customer, email, title, description...">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Status</label>
                        <select name="status"
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl bg-gray-50 text-sm
                                       focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                            <option value="">All</option>
                            <option value="active" @selected(request('status')==='active')>Active</option>
                            <option value="redeemed" @selected(request('status')==='redeemed')>Redeemed</option>
                            <option value="forfeited" @selected(request('status')==='forfeited')>Forfeited</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Loan Date</label>
                        <input type="date" name="loan_date" value="{{ request('loan_date') }}"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl bg-gray-50 text-sm
                                      focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ request('due_date') }}"
                               class="w-full px-3 py-2 border border-gray-200 rounded-xl bg-gray-50 text-sm
                                      focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 border-t border-gray-100 pt-3">
                    <div class="text-xs text-gray-500">
                        Found <span class="font-bold text-yellow-900">{{ $pawnItems->total() }}</span> item{{ $pawnItems->total() === 1 ? '' : 's' }}
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.pawn.index') }}"
                           class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">
                            Clear
                        </a>
                        <button class="rounded-lg bg-yellow-500 px-4 py-1.5 text-xs font-bold text-white hover:bg-yellow-600 shadow-sm">
                            Apply
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Customer / Item</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pawn Date</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Principal</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Interest</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total Due</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-50">
                    @forelse($pawnItems as $item)
                        @php
                            $pawnDate = \Illuminate\Support\Carbon::parse($item->loan_date ?? $item->created_at)->format('M d, Y');
                            $dueDate  = $item->due_date ? \Illuminate\Support\Carbon::parse($item->due_date)->format('M d, Y') : '—';

                            $statusConfig = match($item->status) {
                                'active'     => 'bg-green-100 text-green-700 border-green-200',
                                'redeemed'   => 'bg-blue-100 text-blue-700 border-blue-200',
                                'forfeited'  => 'bg-red-100 text-red-700 border-red-200',
                                default      => 'bg-gray-100 text-gray-700 border-gray-200',
                            };

                            $pics = $item->pictures ?? collect();
                            $picsArr = $pics->map(fn($p) => asset('storage/' . ltrim($p->url, '/')))->values()->toArray();
                        @endphp

                        <tr class="hover:bg-yellow-50/20 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-yellow-900 flex items-center justify-center text-white font-bold text-xs">
                                        {{ mb_substr($item->customer?->name ?? 'W', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-yellow-900">{{ $item->customer?->name ?? 'Walk-in' }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->title }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3 text-sm text-yellow-900 whitespace-nowrap">
                                {{ $pawnDate }}
                            </td>

                            <td class="px-4 py-3 text-right text-sm font-medium text-yellow-900 whitespace-nowrap">
                                ₱{{ number_format($item->price, 2) }}
                            </td>

                            <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                                <div class="font-medium text-yellow-900">₱{{ number_format($item->computed_interest, 2) }}</div>
                            
                            </td>

                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="text-sm font-serif font-bold text-yellow-900">₱{{ number_format($item->to_pay, 2) }}</div>
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-yellow-900">{{ $dueDate }}</div>
                                @if($item->due_date && $item->is_overdue && $item->status === 'active')
                                    <div class="text-[10px] font-bold text-red-600 uppercase tracking-wide">Overdue</div>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusConfig }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button"
                                            class="p-1.5 rounded-lg text-gray-500 hover:text-yellow-700 hover:bg-yellow-50"
                                            title="View Receipt"
                                            @click="openReceipt(@js([
                                                'pawn_id' => str_pad($item->id, 6, '0', STR_PAD_LEFT),
                                                'customer_name' => $item->customer?->name ?? 'Walk-in',
                                                'customer_email' => $item->customer?->email ?? '',
                                                'customer_phone' => $item->customer?->contact_no ?? '',
                                                'title' => $item->title,
                                                'description' => $item->description ?? '',
                                                'pawn_date' => $pawnDate,
                                                'due_date' => $dueDate,
                                                'principal' => (float) $item->price,
                                                'interest' => (float) $item->computed_interest,
                                                'to_pay' => (float) $item->to_pay,
                                                'penalty' => (float) ($item->penalty ?? 0),
                                                'months_overdue' => (int) ($item->months_overdue ?? 0),
                                                'status' => $item->status,
                                                'pictures' => $picsArr,
                                                'download_url' => route('admin.pawn.download', $item),
                                            ]))">
                                        👁️
                                    </button>

                                    <a href="{{ route('admin.pawn.edit', $item) }}"
                                       class="p-1.5 rounded-lg text-gray-500 hover:text-blue-700 hover:bg-blue-50"
                                       title="Edit">
                                        ✏️
                                    </a>

                                    <a href="{{ route('admin.pawn.download', $item) }}"
                                       class="p-1.5 rounded-lg text-gray-500 hover:text-yellow-900 hover:bg-gray-100"
                                       title="Download PDF"
                                       onclick="event.stopPropagation();">
                                        ⬇️
                                    </a>

                                    @if($item->status === 'active')
                                        <form method="POST" action="{{ route('admin.pawn.redeem', $item) }}"
                                              onsubmit="return confirm('Redeem this item and record a transaction?');"
                                              class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="p-1.5 rounded-lg text-gray-500 hover:text-green-700 hover:bg-green-50"
                                                    title="Redeem">
                                                ✅
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.pawn.destroy', $item) }}"
                                          onsubmit="return confirm('Delete this pawn item?');"
                                          class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-red-700 hover:bg-red-50"
                                                title="Delete">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                No pawn items found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 px-4 py-3 bg-gray-50/50">
                {{ $pawnItems->links() }}
            </div>
        </div>

        {{-- Receipt Modal --}}
        <div x-cloak x-show="showReceipt" class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/60" @click="closeReceipt()"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-yellow-900 flex items-center justify-between">
                        <div class="text-white">
                            <div class="text-xs text-gray-300">Pawn Ticket</div>
                            <div class="text-lg font-bold">#<span x-text="receipt.pawn_id"></span></div>
                        </div>
                        <button class="text-gray-300 hover:text-white" @click="closeReceipt()">✖</button>
                    </div>

                    <div class="px-6 py-5 space-y-4 text-sm">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-gray-500 font-bold uppercase">Customer</div>
                                <div class="font-semibold text-yellow-900" x-text="receipt.customer_name"></div>
                                <div class="text-xs text-gray-500" x-text="receipt.customer_email"></div>
                                <div class="text-xs text-gray-500" x-text="receipt.customer_phone"></div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500 font-bold uppercase">Dates</div>
                                <div><span class="text-gray-500">Pawn:</span> <span class="font-semibold" x-text="receipt.pawn_date"></span></div>
                                <div><span class="text-gray-500">Due:</span> <span class="font-semibold" x-text="receipt.due_date"></span></div>
                            </div>
                        </div>

                        <div class="border-t pt-3">
                            <div class="text-xs text-gray-500 font-bold uppercase">Item</div>
                            <div class="font-semibold text-yellow-900" x-text="receipt.title"></div>
                            <div class="text-gray-600 whitespace-pre-line" x-text="receipt.description"></div>
                        </div>

                        <div class="border-t pt-3">
                            <div class="flex justify-between"><span class="text-gray-500">Principal</span><span class="font-semibold" x-text="money(receipt.principal)"></span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Interest</span><span class="font-semibold" x-text="money(receipt.interest)"></span></div>
                            <template x-if="Number(receipt.penalty || 0) > 0">
                                <div class="flex justify-between text-red-600">
                                    <span>Penalty (+3% x <span x-text="receipt.months_overdue"></span> mo)</span>
                                    <span class="font-semibold" x-text="money(receipt.penalty)"></span>
                                </div>
                            </template>
                            <div class="flex justify-between border-t pt-2">
                                <span class="font-bold text-yellow-900">Total Due</span>
                                <span class="font-bold text-yellow-900" x-text="money(receipt.to_pay)"></span>
                            </div>
                        </div>

                        <template x-if="receipt.pictures && receipt.pictures.length">
                            <div class="border-t pt-3">
                                <div class="text-xs text-gray-500 font-bold uppercase mb-2">Photos</div>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(img, idx) in receipt.pictures" :key="idx">
                                        <img :src="img" class="w-16 h-16 rounded-lg border object-cover" />
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="px-6 py-4 border-t bg-gray-50 flex gap-2">
                        <a class="flex-1 text-center px-4 py-2.5 rounded-xl bg-yellow-900 text-white text-sm font-semibold hover:bg-gray-800"
                           :href="receipt.download_url">
                            Download PDF
                        </a>
                        <button class="px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold hover:bg-white"
                                @click="closeReceipt()">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function pawnAdminIndex() {
                return {
                    showReceipt: false,
                    receipt: {},

                    openReceipt(data) {
                        this.receipt = data;
                        this.showReceipt = true;
                        document.body.classList.add('overflow-hidden');
                    },

                    closeReceipt() {
                        this.showReceipt = false;
                        this.receipt = {};
                        document.body.classList.remove('overflow-hidden');
                    },

                    money(v) {
                        return '₱' + Number(v || 0).toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        });
                    },
                }
            }
        </script>

    </div>
</x-admin-layout>
