<x-admin-layout title="Repairs">
    <div class="flex flex-col gap-6"
         x-data="repairIndex()">

        {{-- HEADER --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Repairs</h1>
                <p class="mt-1 text-sm text-gray-500">Manage all repair service jobs.</p>
            </div>

            <a href="{{ route('admin.repairs.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white rounded-lg shadow-sm hover:bg-indigo-700">
                + New Repair
            </a>
        </div>

        {{-- FILTERS --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <form class="grid md:grid-cols-4 gap-4" method="GET">

                <div class="md:col-span-2">
                    <label class="text-xs font-medium text-gray-500">Search (description / customer)</label>
                    <input type="text" name="q" value="{{ request('q') }}"
                           class="mt-1 w-full rounded-lg border-gray-300 shadow-sm text-sm"
                           placeholder="Search...">
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500">Status</label>
                    <select name="status"
                            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm text-sm">
                        <option value="">All</option>
                        <option value="pending"   @selected(request('status')=='pending')>Pending</option>
                        <option value="completed" @selected(request('status')=='completed')>Completed</option>
                        <option value="cancelled" @selected(request('status')=='cancelled')>Cancelled</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button class="bg-gray-900 text-white px-3 py-2 rounded-lg text-xs font-medium">Apply</button>
                    <a href="{{ route('admin.repairs.index') }}"
                       class="border px-3 py-2 rounded-lg text-xs">Clear</a>
                </div>

            </form>
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Customer</th>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-right">Price</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Mark As Complete</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                    <th class="px-4 py-3 text-center">Show</th>
                </tr>
                </thead>

                <tbody class="divide-y">
                @forelse($repairs as $r)
                    <tr class="hover:bg-gray-50">
                        {{-- 1. CUSTOMER (text-left) --}}
                        <td class="px-4 py-3 text-left align-top">
                            <span class="font-medium">{{ $r->customer?->name ?? 'Walk-in' }}</span>
                            <p class="text-xs text-gray-400">{{ $r->customer?->email }}</p>
                        </td>

                        {{-- 2. DESCRIPTION (text-left) --}}
                        <td class="px-4 py-3 text-left align-top">{{ Str::limit($r->description, 40) }}</td>

                        {{-- 3. DATE (text-left) --}}
                        <td class="px-4 py-3 text-left align-top whitespace-nowrap">
                            <span class="font-medium">{{ $r->created_at->format('M d, Y') }}</span>
                            <p class="text-xs text-gray-400">{{ $r->created_at->format('h:i A') }}</p>
                        </td>

                        {{-- 4. PRICE (text-right) --}}
                        <td class="px-4 py-3 text-right align-top whitespace-nowrap">
                            ₱{{ number_format($r->price, 2) }}
                        </td>

                        {{-- 5. STATUS (text-center) --}}
                        <td class="px-4 py-3 text-center align-top">
                            @php
                                $color = match($r->status) {
                                    'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default     => 'bg-gray-50 text-gray-700 border-gray-200'
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full border text-xs font-semibold {{ $color }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                        </td>

                        {{-- 6. MARK AS COMPLETE (text-center) --}}
                        <td class="px-4 py-3 text-center align-top whitespace-nowrap">
                            @if($r->status === 'pending')
                                <form method="POST"
                                      action="{{ route('admin.repairs.complete', $r) }}"
                                      onsubmit="return confirm('Mark this repair as completed?')">
                                    @csrf
                                    <button class="text-emerald-600 text-xs font-semibold hover:text-emerald-800 p-1.5 sm:p-0">
                                        Complete
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">Completed</span>
                            @endif
                        </td>

                        {{-- 7. ACTIONS (Edit/Delete) --}}
                        <td class="px-5 py-3 text-center whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1">
                                {{-- Edit Button (Icon) --}}
                                <a href="{{ route('admin.repairs.edit',$r) }}"
                                   class="text-violet-600 hover:text-violet-700 p-2 rounded-lg hover:bg-violet-100 transition duration-150">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                {{-- Delete Button (Icon) --}}
                                <form method="POST" action="{{ route('admin.repairs.destroy',$r) }}"
                                      onsubmit="return confirm('Are you sure you want to delete this repair?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="text-rose-600 hover:text-rose-700 p-2 rounded-lg hover:bg-rose-100 transition duration-150">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>

                        {{-- 8. SHOW (View Modal) --}}
                        <td class="px-4 py-3 text-center align-top whitespace-nowrap">
                            <button type="button"
                                    class="p-1.5 rounded-full hover:bg-gray-100 text-gray-500"
                                    title="View details"
                                    @click="openModal(@js([
                                            'id'             => $r->id,
                                            'customer_name'  => $r->customer->name ?? 'Walk-in',
                                            'customer_email' => $r->customer->email ?? '',
                                            'description'    => $r->description,
                                            'price'          => (float) $r->price,
                                            'status'         => $r->status,
                                            'image_url'      => $r->picture ? asset($r->picture->url) : null,
                                        ]))">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.5">
                                    <path d="M2.25 12s3-6.75 9.75-6.75S21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z"
                                          stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-gray-500">
                            No repairs found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div class="border-t p-4">
                {{ $repairs->links() }}
            </div>
        </div>

        {{-- MODAL --}}
        <div
            x-cloak
            x-show="showModal"
            @keydown.escape.window="closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">

            <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden"
                 @click.away="closeModal()">

                {{-- Header --}}
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400">Repair</p>
                        <p class="text-sm font-semibold text-gray-900">
                            #<span x-text="repair.id"></span>
                        </p>
                    </div>
                    <button @click="closeModal()" class="p-1.5 rounded-full hover:bg-gray-100 text-gray-500">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="1.5">
                            <path d="M6 18 18 6M6 6l12 12"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-4 py-3 space-y-3 text-sm max-h-[70vh] overflow-y-auto">
                    {{-- Customer --}}
                    <div class="space-y-1">
                        <p class="text-[11px] text-gray-400">Customer</p>
                        <p class="text-sm font-medium text-gray-900" x-text="repair.customer_name"></p>
                        <p class="text-[11px] text-gray-400" x-text="repair.customer_email"></p>
                    </div>

                    {{-- Status + Price --}}
                    <div class="flex items-center justify-between border-t border-gray-100 pt-2">
                        <div>
                            <p class="text-[11px] text-gray-400">Status</p>
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold"
                                  :class="statusBadgeClass(repair.status)"
                                  x-text="statusLabel(repair.status)"></span>
                        </div>

                        <div class="text-right">
                            <p class="text-[11px] text-gray-400">Price</p>
                            <p class="text-sm font-semibold text-gray-900" x-text="money(repair.price)"></p>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="border-t border-gray-100 pt-2">
                        <p class="text-xs font-semibold text-gray-700 mb-1">Description</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line" x-text="repair.description"></p>
                    </div>

                    {{-- Image --}}
                    <div class="border-t border-gray-100 pt-2">
                        <p class="text-xs font-semibold text-gray-700 mb-2">Image</p>

                        <template x-if="repair.image_url">
                            <img :src="repair.image_url"
                                 class="w-full max-w-xs h-auto object-cover rounded border"
                                 alt="Repair image">
                        </template>

                        <template x-if="!repair.image_url">
                            <p class="text-[11px] text-gray-400">No image uploaded.</p>
                        </template>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-4 py-3 border-t bg-gray-50 flex justify-end">
                    <button @click="closeModal()"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 hover:bg-gray-100">
                        Close
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- Alpine helper --}}
    <script>
        function repairIndex() {
            return {
                showModal: false,
                repair: {
                    id: null,
                    customer_name: '',
                    customer_email: '',
                    description: '',
                    price: 0,
                    status: '',
                    image_url: null,
                },
                openModal(data) {
                    this.repair = data;
                    this.showModal = true;
                },
                closeModal() {
                    this.showModal = false;
                },
                money(v) {
                    const n = Number(v || 0);
                    return '₱' + n.toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },
                statusLabel(status) {
                    if (!status) return 'Unknown';
                    return status.charAt(0).toUpperCase() + status.slice(1);
                },
                statusBadgeClass(status) {
                    switch (status) {
                        case 'pending':
                            return 'bg-amber-50 text-amber-700 border-amber-200';
                        case 'completed':
                            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        case 'cancelled':
                            return 'bg-rose-50 text-rose-700 border-rose-200';
                        default:
                            return 'bg-gray-50 text-gray-700 border-gray-200';
                    }
                }
            }
        }
    </script>
</x-admin-layout>
