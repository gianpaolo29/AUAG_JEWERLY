<x-admin-layout title="Pawn Items">
    <div class="flex flex-col gap-6">

        {{-- HEADER + ADD BUTTON --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Pawn Items</h1>
                <p class="mt-1 text-sm text-gray-500">
                    List of all pawned items with principal, interest, due dates, and status.
                </p>
            </div>

            <a href="{{ route('admin.pawn.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                New Pawn Item
            </a>
        </div>

        {{-- FILTERS --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <form method="GET" class="grid gap-4 md:grid-cols-4 items-end">
                {{-- Search --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">
                        Search (Customer / Item name)
                    </label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M15.75 15.75 21 21" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="10.5" cy="10.5" r="6" />
                        </svg>
                        <input type="search" name="q" value="{{ request('q') }}"
                               class="w-full rounded-lg border-gray-300 pl-9 pr-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Search by customer name or item name">
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">
                        Status
                    </label>
                    <select name="status"
                            class="w-full rounded-lg border-gray-300 py-2 px-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="active"    @selected(request('status') === 'active')>Active</option>
                        <option value="redeemed"  @selected(request('status') === 'redeemed')>Redeemed</option>
                        <option value="forfeited" @selected(request('status') === 'forfeited')>Forfeited</option>
                    </select>
                </div>

                {{-- Due date --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">
                        Due date (on or before)
                    </label>
                    <input type="date" name="due_date"
                           value="{{ request('due_date') }}"
                           class="w-full rounded-lg border-gray-300 py-2 px-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- Buttons --}}
                <div class="md:col-span-4 flex items-center justify-between pt-2">
                    <p class="text-xs text-gray-500">
                        Showing:
                        <span class="font-semibold text-gray-700">
                            {{ $pawnItems->total() }} record{{ $pawnItems->total() === 1 ? '' : 's' }}
                        </span>
                    </p>

                    <div class="flex gap-2">
                        <a href="{{ route('admin.pawn.index') }}"
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
                            <th class="px-4 py-3 text-left">Customer</th>
                            <th class="px-4 py-3 text-right">Principal</th>
                            <th class="px-4 py-3 text-right">Interest</th>
                            <th class="px-4 py-3 text-right">To Pay (₱)</th>
                            <th class="px-4 py-3 text-left">Due Date</th>
                            <th class="px-4 py-3 text-center">Overdue</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>

                    {{-- click row to show details --}}
                    <tbody class="divide-y divide-gray-100 text-gray-700" x-data="{ openId: null }">
                        @forelse($pawnItems as $item)
                            {{-- MAIN ROW --}}
                            <tr class="hover:bg-gray-50/80 transition cursor-pointer"
                                @click="openId = openId === {{ $item->id }} ? null : {{ $item->id }}">

                                <td class="px-4 py-3">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $item->customer?->name ?? 'Walk-in / Unknown' }}
                                        </span>
                                        @if($item->customer?->email)
                                            <span class="text-xs text-gray-400">
                                                {{ $item->customer->email }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    ₱{{ number_format($item->price, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    ₱{{ number_format($item->computed_interest, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    ₱{{ number_format($item->to_pay, 2) }}
                                </td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $item->due_date ? $item->due_date->format('M d, Y') : '—' }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($item->is_overdue)
                                        <span class="inline-flex items-center rounded-full bg-rose-50 text-rose-700 px-2.5 py-0.5 text-xs font-semibold border border-rose-100">
                                            Yes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-0.5 text-xs font-semibold border border-emerald-100">
                                            No
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @php
                                        $badge = match($item->status) {
                                            'active'    => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                            'redeemed'  => 'bg-sky-50 text-sky-700 border-sky-100',
                                            'forfeited' => 'bg-rose-50 text-rose-700 border-rose-100',
                                            default     => 'bg-gray-50 text-gray-700 border-gray-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">

                                        {{-- Edit --}}
                                        <a href="{{ route('admin.pawn.edit', $item) }}"
                                           @click.stop
                                           class="text-xs font-medium text-indigo-600 hover:text-indigo-800"
                                           title="Edit">
                                            Edit
                                        </a>

                                        {{-- Redeem (only when active) --}}
                                        @if($item->status === 'active')
                                            <form method="POST"
                                                  action="{{ route('admin.pawn.redeem', $item) }}"
                                                  class="inline"
                                                  @click.stop
                                                  onsubmit="return confirm('Redeem this item and save as a transaction?');">
                                                @csrf
                                                <button type="submit"
                                                        class="text-xs font-medium text-emerald-600 hover:text-emerald-800">
                                                    Redeem
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- DETAILS ROW (DESCRIPTION + PICTURES) --}}
                            <tr x-show="openId === {{ $item->id }}" x-cloak>
                                <td colspan="9" class="bg-gray-50 px-6 py-4">
                                    <div class="flex flex-col lg:flex-row gap-6">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-gray-800 mb-1">Description</h4>
                                            <p class="text-sm text-gray-600">
                                                {{ $item->description ?: 'No description provided.' }}
                                            </p>
                                        </div>

                                        @if($item->pictures->isNotEmpty())
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-gray-800 mb-2">Pictures</h4>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($item->pictures as $pic)
                                                        <img src="{{ asset($pic->url) }}"
                                                             class="w-16 h-16 object-cover rounded border border-gray-200"
                                                             alt="Pawn picture">
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-10 text-center text-sm text-gray-500 bg-gray-50">
                                    No pawn items found. Create a new pawn ticket to get started.
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
                        {{ $pawnItems->firstItem() ?? 0 }}–{{ $pawnItems->lastItem() ?? 0 }}
                    </span>
                    of
                    <span class="font-medium text-gray-700">
                        {{ $pawnItems->total() }}
                    </span>
                    results
                </p>
                <div>
                    {{ $pawnItems->links() }}
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
