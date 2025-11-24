<x-staff-layout title="Pawn Items">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex flex-col gap-6">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-center justify-between gap-4"> {{-- Removed border-b and pb-4 from here --}}
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        My Pawn Items 💍
                    </h1>
                    <p class="mt-1 text-base text-gray-500">
                        All pawned items you have recorded.
                    </p>
                </div>

                <a href="{{ route('staff.pawn.create') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-indigo-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    New Pawn Item
                </a>
            </div>

            {{-- FILTERS & STATS CARD --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <form method="GET" class="grid gap-x-6 gap-y-4 md:grid-cols-4 lg:grid-cols-8 items-end">
                    
                    {{-- SEARCH --}}
                    <div class="md:col-span-2 lg:col-span-3">
                        <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                                fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <circle cx="10.5" cy="10.5" r="6"/>
                                <path d="M15.75 15.75 21 21" stroke-linecap="round"/>
                            </svg>
                            <input type="text" id="q" name="q" value="{{ request('q') }}"
                                class="w-full rounded-xl border-gray-300 pl-10 pr-4 py-2.5 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Customer or item title">
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="lg:col-span-2">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status"
                                class="w-full rounded-xl border-gray-300 py-2.5 px-3 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Statuses</option>
                            <option value="active" @selected(request('status')=='active')>Active</option>
                            <option value="redeemed" @selected(request('status')=='redeemed')>Redeemed</option>
                            <option value="forfeited" @selected(request('status')=='forfeited')>Forfeited</option>
                        </select>
                    </div>

                    {{-- DATE --}}
                    <div class="lg:col-span-2">
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="date" id="due_date" name="due_date" value="{{ request('due_date') }}"
                               class="w-full rounded-xl border-gray-300 py-2.5 px-3 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- BUTTONS --}}
                    <div class="md:col-span-4 lg:col-span-1 flex items-end justify-end gap-2">
                        <a href="{{ route('staff.pawn.index') }}"
                            class="flex-1 text-center sm:flex-none px-4 py-2.5 rounded-xl border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 transition">
                            Clear
                        </a>

                        <button type="submit"
                                class="flex-1 text-center sm:flex-none px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm shadow hover:bg-indigo-700 transition">
                            Apply
                        </button>
                    </div>
                </form>
                
                <div class="border-t border-gray-200 mt-6 pt-4">
                    <p class="text-sm text-gray-500">
                        Showing **{{ $pawnItems->total() }}** record{{ $pawnItems->total() != 1 ? 's' : '' }} matching your filters.
                    </p>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">

                        {{-- TABLE HEADER --}}
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left">Customer / Item</th>
                                <th class="px-6 py-3 text-right">Principal</th>
                                <th class="px-6 py-3 text-right">Interest</th>
                                <th class="px-6 py-3 text-right">Total Due</th> {{-- Renamed 'To Pay' --}}
                                <th class="px-6 py-3 text-left">Due Date</th>
                                <th class="px-6 py-3 text-center">Overdue</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-center">Active/Redeemed</th>
                            </tr>
                        </thead>

                        {{-- TABLE BODY --}}
                        <tbody class="divide-y divide-gray-100 text-gray-700" x-data="{ openId:null }">
                            @forelse($pawnItems as $item)

                                {{-- MAIN ROW --}}
                                <tr class="hover:bg-indigo-50/50 cursor-pointer transition @if($item->is_overdue) border-l-4 border-rose-500 @endif"
                                    @click="openId = openId === {{ $item->id }} ? null : {{ $item->id }}">

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
                                        @if($item->is_overdue)
                                            <span class="px-2.5 py-0.5 text-xs rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-medium">
                                                YES
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 text-xs rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-medium">
                                                NO
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-3 text-center">
                                        @php
                                            $badge = match($item->status) {
                                                'active'    => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                                'redeemed'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'forfeited' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                default     => 'bg-gray-50 text-gray-700 border-gray-200',
                                            };
                                        @endphp
                                        <span class="px-2.5 py-0.5 text-xs rounded-full border font-semibold uppercase {{ $badge }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-3 text-center">
                                        @if($item->status === 'redeemed')
                                            <button disabled
                                            class="text-emerald-600 text-xs font-semibold hover:text-emerald-800 p-1 rounded-lg hover:bg-emerald-50 transition">
                                                    Redeem
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('staff.pawn.redeem', $item->id) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition">
                                                    Redeem
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>

                                {{-- DETAILS ROW --}}
                                <tr x-show="openId === {{ $item->id }}" x-cloak>
                                    <td colspan="7" class="bg-indigo-50/70 px-6 py-4">

                                        <div class="flex flex-col md:flex-row gap-6"> {{-- Changed to md:flex-row for small screens --}}

                                            {{-- DESCRIPTION --}}
                                            <div class="flex-1">
                                                <h4 class="text-xs font-semibold uppercase text-indigo-700 mb-2 tracking-wide">Description</h4>
                                                <p class="text-sm text-gray-800 leading-relaxed">{{ $item->description ?: 'No detailed description provided.' }}</p>
                                            </div>

                                            {{-- IMAGES --}}
                                            @if($item->pictures->isNotEmpty())
                                                <div class="w-full md:w-auto">
                                                    <h4 class="text-xs font-semibold uppercase text-indigo-700 mb-2 tracking-wide">Photos ({{ $item->pictures->count() }})</h4>
                                                    <div class="flex flex-wrap gap-3">
                                                        @foreach ($item->pictures as $pic)
                                                            <img src="{{ asset('storage/'.$pic->url) }}"
                                                                    class="w-20 h-20 object-cover rounded-lg border-2 border-white shadow-md hover:shadow-lg transition">
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                        </div>

                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500 bg-gray-50">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1-2 2v-11a2 2 0 012-2h10a2 2 0 012 2v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No pawn items</h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Get started by adding a new pawn item.
                                        </p>
                                        <div class="mt-6">
                                            <a href="{{ route('staff.pawn.create') }}"
                                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                New Pawn Item
                                            </a>
                                        </div>
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
                        of 
                        <strong>{{ $pawnItems->total() }}</strong> records
                    </p>

                    <div>
                        {{ $pawnItems->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-staff-layout>