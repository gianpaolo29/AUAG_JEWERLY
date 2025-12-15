<x-admin-layout title="Pawn Items">

    {{-- Auto download after redeem --}}
    @if (session('download_transaction_id'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const url = "{{ route('admin.transactions.download', ['transaction' => session('download_transaction_id')]) }}";
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', '');
                document.body.appendChild(link);
                link.click();
                link.remove();
            });
        </script>
    @endif

    {{-- CHANGED: max-w-7xl to w-full, py-8 to py-6 --}}
    <div class="w-full px-4 sm:px-6 py-6"
         x-data="pawnIndex()">

        {{-- HEADER + ADD BUTTON --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div class="space-y-1">
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-gray-900 tracking-tight">Pawn Items</h1>
                <p class="text-sm text-gray-600">
                    Manage pawned items, track payments, and process redemptions
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.pawn.index') }}?export=pdf"
                   class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export
                </a>
                
                <a href="{{ route('admin.pawn.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 transition-colors">
                    <svg class="h-4 w-4 text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    New Pawn Item
                </a>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.05)] border border-gray-100 p-4 sm:p-5 mb-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Search --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                            Search Items
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="search" name="q" value="{{ request('q') }}"
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-xl shadow-sm bg-gray-50
                                          focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm"
                                   placeholder="Customer name, email, item title...">
                        </div>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Status</label>
                        <select name="status"
                                class="block w-full px-3 py-2 border border-gray-200 rounded-xl shadow-sm bg-gray-50
                                       focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm">
                            <option value="">All Status</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="redeemed" @selected(request('status') === 'redeemed')>Redeemed</option>
                            <option value="forfeited" @selected(request('status') === 'forfeited')>Forfeited</option>
                        </select>
                    </div>

                    {{-- Due date --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ request('due_date') }}"
                               class="block w-full px-3 py-2 border border-gray-200 rounded-xl shadow-sm bg-gray-50
                                      focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm">
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 pt-2 border-t border-gray-100 mt-2">
                    <div class="text-xs text-gray-500">
                        Found <span class="font-bold text-gray-900">{{ $pawnItems->total() }}</span> 
                        item{{ $pawnItems->total() === 1 ? '' : 's' }}
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.pawn.index') }}"
                           class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-1.5 text-xs 
                                  font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                            Clear Filters
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-yellow-500 px-4 py-1.5 text-xs 
                                       font-bold text-white hover:bg-yellow-600 transition-colors shadow-sm">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Customer
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Principal
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Interest (3%)
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Total Due
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Due Date
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-50">
                    @forelse($pawnItems as $item)
                        @php
                            $statusConfig = match($item->status) {
                                'active' => ['class' => 'bg-green-100 text-green-700 border-green-200', 'label' => 'Active'],
                                'redeemed' => ['class' => 'bg-blue-100 text-blue-700 border-blue-200', 'label' => 'Redeemed'],
                                'forfeited' => ['class' => 'bg-red-100 text-red-700 border-red-200', 'label' => 'Forfeited'],
                                default => ['class' => 'bg-gray-100 text-gray-700 border-gray-200', 'label' => ucfirst($item->status)],
                            };

                            $pics = $item->pictures ?? collect();
                            $picsArr = $pics->map(fn($p) => asset('storage/' . ltrim($p->url, '/')))->values();
                        @endphp

                        <tr class="hover:bg-yellow-50/20 transition-colors cursor-pointer group"
                            @click="toggleRow({{ $item->id }})">

                            {{-- Customer --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-9 w-9 rounded-full bg-gray-900 flex items-center justify-center text-white font-bold text-xs">
                                        {{ substr($item->customer?->name ?? 'W', 0, 1) }}
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-bold text-gray-900">
                                            {{ $item->customer?->name ?? 'Walk-in Customer' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $item->title }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Principal --}}
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="text-sm font-medium text-gray-900">
                                    ₱{{ number_format($item->price, 2) }}
                                </div>
                            </td>

                            {{-- Interest --}}
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="text-xs font-medium text-gray-500">
                                    ₱{{ number_format($item->computed_interest, 2) }}
                                </div>
                            </td>

                            {{-- Total --}}
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="text-sm font-serif font-bold text-gray-900">
                                    ₱{{ number_format($item->to_pay, 2) }}
                                </div>
                            </td>

                            {{-- Due Date --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ $item->due_date ? $item->due_date->format('M d, Y') : '—' }}
                                </div>
                                @if($item->due_date && $item->is_overdue && $item->status === 'active')
                                    <div class="text-[10px] font-bold text-red-600 uppercase tracking-wide">
                                        Overdue
                                    </div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusConfig['class'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Receipt Button --}}
                                    <button type="button"
                                            class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 
                                                   rounded-lg transition-colors"
                                            title="View Ticket"
                                            @click.stop="openReceipt(@js([
                                                'pawn_id' => str_pad($item->id, 6, '0', STR_PAD_LEFT),
                                                'title' => $item->title,
                                                'description' => $item->description ?? '',
                                                'status' => $item->status,
                                                'customer_name' => $item->customer?->name ?? 'Walk-in Customer',
                                                'customer_email' => $item->customer?->email ?? '',
                                                'customer_phone' => $item->customer?->contact_no ?? '',
                                                'address' => 'Calamba, Laguna', // Static for now
                                                'principal' => (float) $item->price,
                                                'interest' => (float) $item->computed_interest,
                                                'to_pay' => (float) $item->to_pay,
                                                'pawn_date' => $item->created_at ? $item->created_at->format('M d, Y') : '—',
                                                'due_date' => $item->due_date ? $item->due_date->format('M d, Y') : '—',
                                                'is_overdue' => (bool) $item->is_overdue,
                                                'pictures' => $picsArr,
                                            ]))">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </button>

                                    {{-- Edit Button --}}
                                    <a href="{{ route('admin.pawn.edit', $item) }}"
                                       class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 
                                              rounded-lg transition-colors"
                                       @click.stop
                                       title="Edit">
                                       <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>

                                    {{-- Redeem --}}
                                    @if($item->status === 'active')
                                        <form method="POST"
                                              action="{{ route('admin.pawn.redeem', $item) }}"
                                              class="inline"
                                              @click.stop
                                              onsubmit="return confirm('Confirm redemption? This will mark item as redeemed and record a transaction.');">
                                            @csrf
                                            <button type="submit"
                                                    class="p-1.5 text-gray-400 hover:text-green-600 
                                                           hover:bg-green-50 rounded-lg transition-colors"
                                                    title="Redeem Item">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- Expanded Details Row --}}
                        <tr x-show="openId === {{ $item->id }}" x-cloak class="bg-gray-50 border-b border-gray-200">
                            <td colspan="7" class="px-6 py-4">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Item Description</h4>
                                        <p class="text-sm text-gray-700 bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                            {{ $item->description ?: 'No description provided.' }}
                                        </p>
                                    </div>

                                    @if($pics->isNotEmpty())
                                        <div>
                                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Attached Photos</h4>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($pics as $pic)
                                                    <a href="{{ asset('storage/' . ltrim($pic->url, '/')) }}" target="_blank">
                                                        <img src="{{ asset('storage/' . ltrim($pic->url, '/')) }}"
                                                             class="w-16 h-16 object-cover rounded-lg border border-gray-200 hover:border-yellow-400 transition-colors bg-white shadow-sm"
                                                             alt="Pawn item photo">
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="text-sm font-medium">No pawn items found.</p>
                                    <a href="{{ route('admin.pawn.create') }}" class="text-xs text-yellow-600 hover:underline mt-1 font-bold">Create new entry</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-gray-100 px-4 py-3 bg-gray-50/50">
                {{ $pawnItems->links() }}
            </div>
        </div>

        {{-- RECEIPT MODAL --}}
        <div x-cloak x-show="showReceipt" class="relative z-50">
            {{-- Backdrop --}}
            <div x-show="showReceipt"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/60 backdrop-blur-sm"
                 @click="closeReceipt()"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="showReceipt"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="relative w-full max-w-3xl bg-white rounded-xl shadow-2xl overflow-hidden">

                        {{-- Modal Header --}}
                        <div class="bg-gray-900 px-6 py-4 flex items-center justify-between">
                            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Pawn Ticket Preview
                            </h2>
                            <button @click="closeReceipt()" class="text-gray-400 hover:text-white">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        {{-- Printable Content Area --}}
                        <div class="p-8 bg-white" x-ref="receiptContent">
                            
                            {{-- TICKET HEADER --}}
                            <div class="border-b-2 border-gray-800 pb-4 mb-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h1 class="text-2xl font-serif font-bold text-gray-900 tracking-wide">AUAG JEWELRY</h1>
                                        <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mt-1">Pawnshop & Jewelry</p>
                                        <p class="text-xs text-gray-500 mt-2">123 Main Street, Calamba, Laguna</p>
                                        <p class="text-xs text-gray-500">Tel: (049) 555-0123</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="inline-block border-2 border-gray-800 px-3 py-1 mb-2">
                                            <p class="text-xs font-bold text-gray-900 uppercase">Ticket No.</p>
                                            <p class="text-xl font-mono font-bold text-gray-900" x-text="receipt.pawn_id"></p>
                                        </div>
                                        <p class="text-xs text-gray-500">Date: <span class="font-bold text-gray-900" x-text="receipt.pawn_date"></span></p>
                                    </div>
                                </div>
                            </div>

                            {{-- 2-COL LAYOUT --}}
                            <div class="grid grid-cols-2 gap-8 mb-6 text-sm">
                                
                                {{-- PAWNER INFO --}}
                                <div>
                                    <h3 class="font-bold text-gray-900 uppercase border-b border-gray-200 pb-1 mb-2 text-xs">Pawner Information</h3>
                                    <table class="w-full text-xs">
                                        <tr>
                                            <td class="text-gray-500 py-1 w-16">Name:</td>
                                            <td class="font-bold text-gray-900 uppercase" x-text="receipt.customer_name"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-500 py-1">Contact:</td>
                                            <td class="text-gray-900" x-text="receipt.customer_phone"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-500 py-1">Address:</td>
                                            <td class="text-gray-900" x-text="receipt.address"></td>
                                        </tr>
                                    </table>
                                </div>

                                {{-- LOAN DETAILS --}}
                                <div>
                                    <h3 class="font-bold text-gray-900 uppercase border-b border-gray-200 pb-1 mb-2 text-xs">Loan Details</h3>
                                    <table class="w-full text-xs">
                                        <tr>
                                            <td class="text-gray-500 py-1">Maturity Date:</td>
                                            <td class="font-bold text-gray-900" x-text="receipt.due_date"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-gray-500 py-1">Interest Rate:</td>
                                            <td class="text-gray-900">3% Monthly</td>
                                        </tr>
                                        <tr x-show="receipt.status !== 'active'">
                                            <td class="text-gray-500 py-1">Status:</td>
                                            <td class="font-bold uppercase" x-text="receipt.status"></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            {{-- ITEM DETAILS --}}
                            <div class="border border-gray-200 rounded p-4 mb-6">
                                <h3 class="font-bold text-gray-900 uppercase text-xs mb-2">Item Description</h3>
                                <p class="text-sm font-bold text-gray-900" x-text="receipt.title"></p>
                                <p class="text-xs text-gray-600 mt-1" x-text="receipt.description"></p>
                            </div>

                            {{-- FINANCIALS --}}
                            <div class="flex justify-end mb-8">
                                <div class="w-1/2">
                                    <div class="flex justify-between py-1 border-b border-gray-100">
                                        <span class="text-xs text-gray-600">Principal Amount</span>
                                        <span class="text-sm font-bold text-gray-900" x-text="money(receipt.principal)"></span>
                                    </div>
                                    <div class="flex justify-between py-1 border-b border-gray-100">
                                        <span class="text-xs text-gray-600">Advanced Interest</span>
                                        <span class="text-sm text-gray-900" x-text="money(receipt.interest)"></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b-2 border-gray-900 mt-1">
                                        <span class="text-sm font-bold text-gray-900 uppercase">Net Proceeds</span>
                                        <span class="text-lg font-serif font-bold text-gray-900" x-text="money(receipt.principal - receipt.interest)"></span>
                                    </div>
                                    <div class="flex justify-between py-1 mt-1">
                                        <span class="text-[10px] text-gray-500">Redemption Amount (Est.)</span>
                                        <span class="text-xs font-bold text-gray-500" x-text="money(receipt.to_pay)"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- TERMS & CONDITIONS --}}
                            <div class="text-[9px] text-justify text-gray-500 leading-tight border-t border-gray-200 pt-4 mb-8">
                                <p class="mb-1 font-bold">TERMS AND CONDITIONS OF PAWN:</p>
                                <p>1. The pawner hereby accepts the pawnshop's appraisal. 2. The interest rate is 3% per month. 3. This ticket must be presented for redemption. 4. In case of loss of this ticket, the pawner must immediately notify the pawnshop in writing. 5. The pawner warrants that he/she is the owner of the item(s) pledged. 6. Items not redeemed within 90 days after maturity date may be sold at public auction.</p>
                            </div>

                            {{-- SIGNATURES --}}
                            <div class="grid grid-cols-2 gap-12 mt-8">
                                <div class="text-center">
                                    <div class="border-b border-gray-800 mb-2"></div>
                                    <p class="text-xs font-bold uppercase text-gray-900">Pawner's Signature</p>
                                </div>
                                <div class="text-center">
                                    <div class="border-b border-gray-800 mb-2"></div>
                                    <p class="text-xs font-bold uppercase text-gray-900">Appraiser's Signature</p>
                                </div>
                            </div>

                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                            <button @click="closeReceipt()" 
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Close
                            </button>
                            <button @click="printReceipt()" 
                                    class="px-4 py-2 text-sm font-bold text-white bg-gray-900 rounded-lg hover:bg-gray-800 shadow-lg flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Print Ticket
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Alpine.js Logic --}}
        <script>
            function pawnIndex() {
                return {
                    openId: null,
                    showReceipt: false,
                    receipt: {},

                    toggleRow(id) {
                        this.openId = this.openId === id ? null : id;
                    },

                    openReceipt(data) {
                        this.receipt = data;
                        this.showReceipt = true;
                        document.body.classList.add('overflow-hidden');
                    },

                    closeReceipt() {
                        this.showReceipt = false;
                        document.body.classList.remove('overflow-hidden');
                    },

                    money(v) {
                        return '₱' + Number(v || 0).toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        });
                    },

                    printReceipt() {
                        const content = this.$refs.receiptContent.innerHTML;
                        const printWindow = window.open('', '_blank', 'width=800,height=600');
                        
                        printWindow.document.write(`
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <title>Pawn Ticket #${this.receipt.pawn_id}</title>
                                <style>
                                    /* Minimal CSS for clean printing without CDN */
                                    body { 
                                        font-family: 'Times New Roman', serif; 
                                        color: #000; 
                                        padding: 20px; 
                                        max-width: 800px;
                                        margin: 0 auto;
                                    }
                                    .flex { display: flex; }
                                    .justify-between { justify-content: space-between; }
                                    .items-start { align-items: flex-start; }
                                    .text-right { text-align: right; }
                                    .text-center { text-align: center; }
                                    .font-bold { font-weight: bold; }
                                    .uppercase { text-transform: uppercase; }
                                    .border-b-2 { border-bottom: 2px solid #000; }
                                    .border-b { border-bottom: 1px solid #ccc; }
                                    .mb-6 { margin-bottom: 24px; }
                                    .pb-4 { padding-bottom: 16px; }
                                    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }
                                    table { width: 100%; border-collapse: collapse; }
                                    td { padding: 4px 0; vertical-align: top; }
                                    .text-xs { font-size: 10px; }
                                    .text-sm { font-size: 12px; }
                                    .text-xl { font-size: 20px; }
                                    .text-2xl { font-size: 24px; }
                                    .text-gray-500 { color: #555; }
                                    
                                    /* Print specific adjustments */
                                    @media print {
                                        body { padding: 0; margin: 0; }
                                        @page { margin: 1cm; size: auto; }
                                    }
                                </style>
                            </head>
                            <body>
                                ${content}
                                <script>
                                    window.onload = function() {
                                        window.print();
                                        setTimeout(() => window.close(), 500);
                                    };
                                <\/script>
                            </body>
                            </html>
                        `);
                        printWindow.document.close();
                    }
                }
            }
        </script>

    </div>
</x-admin-layout>