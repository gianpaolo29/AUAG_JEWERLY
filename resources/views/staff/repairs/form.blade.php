<x-staff-layout :title="$isEdit ? 'Edit Repair' : 'New Repair'">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="repairForm({
            customers: @js($customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name])),
            selectedCustomer: {{ old('customer_id', $repair->customer_id) ? (int) old('customer_id', $repair->customer_id) : 'null' }},
            existingImage: @js($isEdit && $repair->picture ? asset('storage/'.$repair->picture->url) : '')
         })"
         x-init="init()">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $isEdit ? 'Edit Repair' : 'New Repair' }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">Fill out the repair information below.</p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('staff.repairs.index') }}"
                   class="px-4 py-2 text-gray-700 rounded-lg bg-gray-100 hover:bg-gray-200">
                    Cancel
                </a>
                <button type="submit" form="repair-form"
                        class="px-5 py-2 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700">
                    {{ $isEdit ? 'Save Changes' : 'Create Repair' }}
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-red-700 text-sm mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-xl border border-gray-100 p-6">
            <form id="repair-form"
                  method="POST"
                  action="{{ $isEdit ? route('staff.repairs.update', $repair) : route('staff.repairs.store') }}"
                  enctype="multipart/form-data"
                  class="space-y-6">

                @csrf
                @if($isEdit) @method('PUT') @endif

                <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Repair Details</h2>

                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700">Customer *</label>

                    <input type="hidden" name="customer_id" x-model="selectedCustomer">

                    <div class="relative">
                        <button type="button" @click="open = !open"
                                class="w-full border rounded-lg px-3 py-2 flex justify-between items-center text-sm bg-white">
                            <span x-text="selectedName || 'Select customer'"></span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </button>

                        <div x-show="open" x-cloak @click.outside="open = false"
                             class="absolute z-20 mt-1 w-full bg-white border rounded-lg shadow-lg">
                            <div class="p-2 border-b">
                                <input type="text" x-model="search" placeholder="Search..."
                                       class="w-full border rounded px-2 py-1 text-sm">
                            </div>

                            <ul class="max-h-60 overflow-y-auto">
                                <template x-for="cust in filtered" :key="cust.id">
                                    <li>
                                        <button type="button"
                                                @click="select(cust)"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50">
                                            <span x-text="cust.name"></span>
                                        </button>
                                    </li>
                                </template>

                                <template x-if="filtered.length === 0">
                                    <li class="px-3 py-2 text-xs text-gray-400">No customers found</li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    @error('customer_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Description *</label>
                    <textarea name="description" rows="3"
                              class="w-full border rounded-lg text-sm px-3 py-2"
                              required>{{ old('description', $repair->description) }}</textarea>
                    @error('description') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Price (₱) *</label>
                    <input type="number" name="price" step="0.01" min="0"
                           value="{{ old('price', $repair->price) }}"
                           class="w-full border rounded-lg text-sm px-3 py-2" required>
                    @error('price') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Status</label>
                    <select name="status"
                            class="w-full border rounded-lg text-sm px-3 py-2">
                        <option value="pending"   @selected(old('status', $repair->status)=='pending')>Pending</option>
                        <option value="completed" @selected(old('status', $repair->status)=='completed')>Completed</option>
                        <option value="cancelled" @selected(old('status', $repair->status)=='cancelled')>Cancelled</option>
                    </select>
                    @error('status') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">Repair Image</label>
                    <input type="file" name="image" accept="image/*"
                           @change="
                               const f = $event.target.files[0];
                               if (f) preview = URL.createObjectURL(f);
                           "
                           class="block w-full text-sm text-gray-600">

                    <template x-if="preview">
                        <img :src="preview" class="w-24 h-24 rounded-lg mt-2 object-cover border">
                    </template>
                </div>

            </form>
        </div>
    </div>

    <script>
        function repairForm({ customers, selectedCustomer, existingImage }) {
            return {
                customers,
                selectedCustomer,
                selectedName: '',
                search: '',
                open: false,
                preview: existingImage,

                init() {
                    if (this.selectedCustomer) {
                        const c = this.customers.find(x => x.id === this.selectedCustomer);
                        this.selectedName = c ? c.name : '';
                    }
                },

                get filtered() {
                    if (!this.search) return this.customers;
                    const term = this.search.toLowerCase();
                    return this.customers.filter(c => c.name.toLowerCase().includes(term));
                },

                select(cust) {
                    this.selectedCustomer = cust.id;
                    this.selectedName = cust.name;
                    this.open = false;
                }
            };
        }
    </script>

</x-staff-layout>
