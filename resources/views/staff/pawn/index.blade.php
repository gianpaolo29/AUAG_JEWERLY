<x-staff-layout title="Pawn Items">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex flex-col gap-6">

            {{-- HEADER --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-b pb-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">My Pawn Items 💍</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        All pawned items you have recorded.
                    </p>
                </div>

                <a href="{{ route('staff.pawn.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-indigo-700 transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    New Pawn Item
                </a>
            </div>

            {{-- FILTERS --}}
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <form method="GET" class="grid gap-4 md:grid-cols-4 lg:grid-cols-6 items-end">

                    {{-- SEARCH --}}
                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                                 fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="10.5" cy="10.5" r="6"/>
                                <path d="M15.75 15.75 21 21" stroke-linecap="round"/>
                            </svg>

                            <input type="text" name="q" value="{{ request('q') }}"
                                   class="w-full rounded-xl border-gray-300 pl-10 pr-4 py-2.5 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Customer or item title">
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                                class="w-full rounded-xl border-gray-300 py-2.5 px-3 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="active" @selected(request('status')=='active')>Active</option>
                            <option value="redeemed" @selected(request('status')=='redeemed')>Redeemed</option>
                            <option value="forfeited" @selected(request('status')=='forfeited')>Forfeited</option>
                        </select>
                    </div>

                    {{-- DATE --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" value="{{ request('due_date') }}"
                               class="w-full rounded-xl border-gray-300 py-2.5 px-3 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- BUTTONS --}}
                    <div class="md:col-span-4 lg:col-span-2 flex items-center justify-end gap-2 pt-2">
                        <a href="{{ route('staff.pawn.index') }}"
                           class="px-4 py-2.5 rounded-xl border border-gray-300 text-sm text-gray-700 hover:bg-gray-50">
                            Clear
                        </a>

                        <button type="submit"
                                class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm shadow hover:bg-indigo-700">
                            Apply Filters
                        </button>
                    </div>

                    <p class="md:col-span-4 lg:col-span-6 text-sm text-gray-500 pt-1">
                        Showing <strong>{{ $pawnItems->total() }}</strong> record{{ $pawnItems->total() != 1 ? 's' : '' }}.
                    </p>
                </form>
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
                                <th class="px-6 py-3 text-right">To Pay</th>
                                <th class="px-6 py-3 text-left">Due Date</th>
                                <th class="px-6 py-3 text-center">Overdue</th>
                                <th class="px-6 py-3 text-center">Status</th>
                            </tr>
                        </thead>

                        {{-- TABLE BODY --}}
                        <tbody class="divide-y divide-gray-100 text-gray-700" x-data="{ openId:null }">
                            @forelse($pawnItems as $item)

                                {{-- MAIN ROW --}}
                                <tr class="hover:bg-gray-50 cursor-pointer"
                                    @click="openId = openId === {{ $item->id }} ? null : {{ $item->id }}">

                                    <td class="px-6 py-3">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-900">
                                                {{ $item->customer?->name ?? 'Walk-in' }}
                                            </span>
                                            <span class="text-xs text-gray-500">{{ $item->title }}</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-3 text-right">
                                        ₱{{ number_format($item->price, 2) }}
                                    </td>

                                    <td class="px-6 py-3 text-right text-orange-600 font-medium">
                                        ₱{{ number_format($item->computed_interest, 2) }}
                                    </td>

                                    <td class="px-6 py-3 text-right font-bold text-green-700">
                                        ₱{{ number_format($item->to_pay, 2) }}
                                    </td>

                                    <td class="px-6 py-3">
                                        {{ $item->due_date?->format('M d, Y') ?? '—' }}
                                    </td>

                                    <td class="px-6 py-3 text-center">
                                        @if($item->is_overdue)
                                            <span class="px-2.5 py-0.5 text-xs rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                                                Yes
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 text-xs rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                No
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
                                        <span class="px-2.5 py-0.5 text-xs rounded-full border font-semibold {{ $badge }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                </tr>

                                {{-- DETAILS ROW --}}
                                <tr x-show="openId === {{ $item->id }}" x-cloak>
                                    <td colspan="7" class="bg-indigo-50 px-6 py-4">

                                        <div class="flex flex-col lg:flex-row gap-6">

                                            {{-- DESCRIPTION --}}
                                            <div class="flex-1">
                                                <h4 class="text-xs font-semibold uppercase text-indigo-700 mb-1">Description</h4>
                                                <p class="text-sm text-gray-700">{{ $item->description ?: 'No description.' }}</p>
                                            </div>

                                            {{-- IMAGES --}}
                                            @if($item->pictures->isNotEmpty())
                                                <div class="flex-1">
                                                    <h4 class="text-xs font-semibold uppercase text-indigo-700 mb-2">Photos ({{ $item->pictures->count() }})</h4>
                                                    <div class="flex flex-wrap gap-3">
                                                        @foreach ($item->pictures as $pic)
                                                            <img src="{{ asset('storage/'.$pic->url) }}"
                                                                 class="w-20 h-20 object-cover rounded-lg border bg-white shadow">
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
                                        No pawn items recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Showing 
                        <strong>{{ $pawnItems->firstItem() }}</strong>–<strong>{{ $pawnItems->lastItem() }}</strong>
                        of 
                        <strong>{{ $pawnItems->total() }}</strong>
                    </p>

                    <div>
                        {{ $pawnItems->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-staff-layout>
