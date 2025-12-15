<x-staff-layout title="Repairs">
    @if (session('download_repair_id'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const url = @json(route('staff.repairs.download', session('download_repair_id')));
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = url;
                document.body.appendChild(iframe);
            });
        </script>
    @endif
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex flex-col gap-6" x-data="repairIndex()">

            <div class="flex items-center justify-between border-b pb-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900">Repair Jobs 🛠️</h1>
                    <p class="mt-1 text-sm text-gray-500">All repair service work you have recorded.</p>
                </div>

                <a href="{{ route('staff.repairs.create') }}"
                   class="inline-flex items-center gap-2 bg-yellow-600 px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-lg hover:bg-yellow-700 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    New Repair
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <form class="grid md:grid-cols-4 gap-4 items-end" method="GET">

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search (description / customer)</label>
                        <input type="text" name="q" value="{{ request('q') }}"
                               class="w-full rounded-xl border-gray-300 shadow-sm text-sm py-2.5 focus:ring-yellow-500 focus:border-yellow-500"
                               placeholder="Search...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                                class="w-full rounded-xl border-gray-300 shadow-sm text-sm py-2.5 focus:ring-yellow-500 focus:border-yellow-500">
                            <option value="">All</option>
                            <option value="pending"   @selected(request('status')=='pending')>Pending</option>
                            <option value="completed" @selected(request('status')=='completed')>Completed</option>
                            <option value="cancelled" @selected(request('status')=='cancelled')>Cancelled</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                                class="w-full bg-yellow-600 text-white px-3 py-2.5 rounded-xl text-sm font-semibold shadow-md hover:bg-yellow-700 transition">
                            Apply Filters
                        </button>

                        <a href="{{ route('staff.repairs.index') }}"
                           class="w-full text-center border border-gray-300 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Clear
                        </a>
                    </div>

                    <p class="md:col-span-4 text-sm text-gray-500 pt-1">
                        Showing <strong>{{ $repairs->total() }}</strong> records
                    </p>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left w-40">Customer</th>
                                <th class="px-6 py-3 text-left min-w-[200px]">Description</th>
                                <th class="px-6 py-3 text-left w-32">Date Logged</th>
                                <th class="px-6 py-3 text-right w-24">Price</th>
                                <th class="px-6 py-3 text-center w-24">Status</th>
                                <th class="px-6 py-3 text-center w-24">Complete?</th>
                                <th class="px-6 py-3 text-center w-20">Actions</th>
                                <th class="px-6 py-3 text-center w-20">Show</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse($repairs as $r)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <span class="font-medium text-gray-900">{{ $r->customer?->name ?? 'Walk-in' }}</span>
                                        <p class="text-xs text-gray-500">{{ $r->customer?->email }}</p>
                                    </td>

                                    <td class="px-6 py-3 text-gray-700">{{ Str::limit($r->description, 50) }}</td>

                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="font-medium text-gray-800">{{ $r->created_at->format('M d, Y') }}</span>
                                        <p class="text-xs text-gray-500">{{ $r->created_at->format('h:i A') }}</p>
                                    </td>

                                    <td class="px-6 py-3 text-right font-semibold text-gray-800">
                                        ₱{{ number_format($r->price, 2) }}
                                    </td>

                                    <td class="px-6 py-3 text-center">
                                        @php
                                            $color = match($r->status) {
                                                'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                                'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                default     => 'bg-gray-50 text-gray-700 border-gray-200',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $color }}">
                                            {{ ucfirst($r->status) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-3 text-center">
                                        @if($r->status === 'pending')
                                            <form method="POST"
                                                  action="{{ route('staff.repairs.complete', $r) }}"
                                                  onsubmit="return confirm('Mark this repair as completed?')">
                                                @csrf
                                                <button class="text-emerald-600 text-xs font-semibold hover:text-emerald-800 p-1 rounded-lg hover:bg-emerald-50 transition">
                                                    Complete
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">Done</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-3 text-center">
                                        <div class="inline-flex items-center gap-1">
                                            <a href="{{ route('staff.repairs.edit', $r) }}"
                                               class="text-yellow-600 hover:text-yellow-800 p-1 rounded-lg hover:bg-yellow-50 transition">
                                                ✏️
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('staff.repairs.destroy', $r) }}"
                                                  onsubmit="return confirm('Delete this repair?')">
                                                @csrf @method('DELETE')
                                                <button class="text-rose-600 hover:text-rose-700 p-1 rounded-lg hover:bg-rose-50 transition">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                    <td class="px-6 py-3 text-center">
                                        <button type="button"
                                                class="p-1.5 rounded-full hover:bg-gray-100 text-gray-500 hover:text-gray-700 transition"
                                                @click="openModal(@js([
                                                    'id' => $r->id,
                                                    'customer_name' => $r->customer->name ?? 'Walk-in',
                                                    'customer_email' => $r->customer->email ?? '',
                                                    'description' => $r->description,
                                                    'price' => (float) $r->price,
                                                    'status' => $r->status,
                                                    'image_url' => $r->picture ? asset('storage/'.$r->picture->url) : null,
                                                ]))">
                                            👁️
                                        </button>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-gray-500">
                                        No repair jobs found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Showing
                        <strong>{{ $repairs->firstItem() ?? 0 }}</strong>–
                        <strong>{{ $repairs->lastItem() ?? 0 }}</strong>
                        of
                        <strong>{{ $repairs->total() }}</strong> results
                    </p>

                    <div>{{ $repairs->links() }}</div>
                </div>
            </div>

            <div x-cloak x-show="showModal"
                 x-transition
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                 @click.self="closeModal()"
                 @keydown.escape.window="closeModal()">

                <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden">

                    <div class="flex items-center justify-between px-5 py-3 border-b bg-gray-50">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">Repair Job ID</p>
                            <p class="text-lg font-bold text-gray-900">#<span x-text="repair.id"></span></p>
                        </div>
                        <button @click="closeModal()" class="p-2 rounded-full hover:bg-gray-100 text-gray-600">
                            ✖
                        </button>
                    </div>

                    <div class="px-5 py-4 space-y-4 text-sm max-h-[70vh] overflow-y-auto">

                        <div class="pb-2 border-b">
                            <p class="text-xs font-semibold text-gray-700">Customer</p>
                            <p class="text-sm font-medium text-gray-900" x-text="repair.customer_name"></p>
                            <p class="text-xs text-gray-500" x-text="repair.customer_email"></p>
                        </div>

                        <div class="flex justify-between">
                            <div>
                                <p class="text-xs font-semibold text-gray-700">Status</p>
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                                      :class="statusBadgeClass(repair.status)"
                                      x-text="statusLabel(repair.status)"></span>
                            </div>

                            <div class="text-right">
                                <p class="text-xs font-semibold text-gray-700">Service Price</p>
                                <p class="text-lg font-bold text-gray-900" x-text="money(repair.price)"></p>
                            </div>
                        </div>

                        <div class="border-t pt-3">
                            <p class="text-xs font-semibold text-gray-700">Description</p>
                            <p class="text-sm text-gray-700 whitespace-pre-line mt-1" x-text="repair.description"></p>
                        </div>

                        <div class="border-t pt-3">
                            <p class="text-xs font-semibold text-gray-700 mb-2">Item Photo</p>
                            <template x-if="repair.image_url">
                                <img :src="repair.image_url" class="w-full h-32 object-cover rounded-lg border shadow-sm">
                            </template>
                            <template x-if="!repair.image_url">
                                <p class="text-xs text-gray-500">No image uploaded for this job.</p>
                            </template>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function repairIndex() {
            return {
                showModal: false,
                repair: {},
                openModal(data) {
                    this.repair = data;
                    this.showModal = true;
                },
                closeModal() {
                    this.showModal = false;
                },
                money(v) {
                    return '₱' + Number(v || 0).toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },
                statusLabel(s) {
                    return s.charAt(0).toUpperCase() + s.slice(1);
                },
                statusBadgeClass(s) {
                    return {
                        pending: 'bg-amber-50 text-amber-700 border-amber-200',
                        completed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        cancelled: 'bg-rose-50 text-rose-700 border-rose-200',
                    }[s] || 'bg-gray-50 text-gray-700 border-gray-200';
                }
            }
        }
    </script>

</x-staff-layout>
