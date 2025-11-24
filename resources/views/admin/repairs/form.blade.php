<x-admin-layout :title="$isEdit ? 'Edit Repair' : 'New Repair'">

    <div class="flex flex-col gap-6"
         x-data="repairForm({
            customers: @js($customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name])),
            selectedCustomer: {{ old('customer_id', $repair->customer_id) ? (int) old('customer_id', $repair->customer_id) : 'null' }},
            existingImage: @js($isEdit && $repair->picture ? asset('storage/'.$repair->picture->url) : '')
         })"
         x-init="init()"
    >

        {{-- HEADER --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $isEdit ? 'Edit Repair' : 'New Repair' }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Manage repair details including customer, description, price, and status.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.repairs.index') }}"
                   class="px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100">
                    Cancel
                </a>
                <button type="submit" form="repair-form"
                        class="px-5 py-2 rounded-lg bg-orange-600 text-white font-semibold hover:bg-orange-700 shadow-lg">
                    {{ $isEdit ? 'Save Changes' : 'Create Repair' }}
                </button>
            </div>
        </div>

        {{-- ERRORS --}}
        @if ($errors->any())
            <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-red-700 text-sm">
                <p class="font-semibold mb-2">Please fix the following:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORM --}}
        <div class="bg-white rounded-xl shadow-xl border border-gray-100 p-6">
            <form id="repair-form"
                  method="POST"
                  action="{{ $isEdit ? route('admin.repairs.update', $repair) : route('admin.repairs.store') }}"
                  enctype="multipart/form-data"
                  class="space-y-6">

                @csrf
                @if($isEdit) @method('PUT') @endif

                <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Repair Details</h2>

                {{-- CUSTOMER (SEARCHABLE DROPDOWN) --}}
                <div class="space-y-1">
                    <label class="text-sm font-medium text-gray-700">
                        Customer <span class="text-red-500">*</span>
                    </label>

                    <input type="hidden" name="customer_id" x-model="selectedCustomer" required>

                    <div class="relative">
                        <button type="button" @click="open = !open"
                                class="w-full border rounded-lg px-3 py-2 flex justify-between items-center text-sm bg-white">
                            <span x-text="selectedName || 'Select customer'"></span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        {{-- DROPDOWN --}}
                        <div x-show="open"
                             @click.outside="open = false"
                             x-cloak
                             class="absolute z-20 mt-1 w-full bg-white border rounded-lg shadow-xl">

                            <div class="p-2 border-b">
                                <input type="text" x-model="search"
                                       placeholder="Search customer..."
                                       class="w-full border rounded px-2 py-1 text-sm">
                            </div>

                            <ul class="max-h-60 overflow-y-auto">
                                <template x-for="cust in filtered" :key="cust.id">
                                    <li>
                                        <button type="button"
                                                @click="select(cust)"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-orange-50">
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
                    @error('customer_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- DESCRIPTION --}}
                <div>
                    <label class="text-sm font-medium text-gray-700">Description *</label>
                    <textarea name="description" rows="3" required
                              class="w-full border rounded-lg text-sm px-3 py-2"
                              placeholder="Repair, Resize, Adjust">{{ old('description', $repair->description) }}</textarea>
                    @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- PRICE --}}
                <div>
                    <label class="text-sm font-medium text-gray-700">Price (₱) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">₱</span>
                        <input type="number" name="price" step="0.01" min="0" required
                               value="{{ old('price', $repair->price) }}"
                               class="w-full pl-7 border rounded-lg text-sm px-3 py-2"
                               placeholder="0.00">
                    </div>
                    @error('price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- STATUS --}}
                <div>
                    <label class="text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="w-full border rounded-lg text-sm px-3 py-2">
                        <option value="pending"   @selected(old('status', $repair->status)=='pending')>Pending</option>
                        <option value="completed" @selected(old('status', $repair->status)=='completed')>Completed</option>
                        <option value="cancelled" @selected(old('status', $repair->status)=='cancelled')>Cancelled</option>
                    </select>
                    @error('status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- IMAGE UPLOAD + PREVIEW --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">Repair Image</label>

                    <input type="file" name="image" accept="image/*"
                           @change="
                               const file = $event.target.files[0];
                               if (file) preview = URL.createObjectURL(file);
                           "
                           class="block w-full text-sm text-gray-600 file:bg-gray-100 file:px-3 file:py-1 file:rounded file:border-0">

                    <template x-if="preview">
                        <img :src="preview" class="w-24 h-24 mt-2 rounded-lg object-cover border" alt="Repair image preview">
                    </template>
                </div>

            </form>
        </div>
    </div>

    {{-- ALPINE SCRIPT --}}
    <script>
        function repairForm({ customers, selectedCustomer, existingImage }) {
            return {
                customers,
                selectedCustomer,
                selectedName: '',
                search: '',
                open: false,
                existingImage,
                preview: existingImage,

                init() {
                    if (this.selectedCustomer) {
                        const found = this.customers.find(c => c.id === this.selectedCustomer);
                        this.selectedName = found ? found.name : '';
                    }
                },

                get filtered() {
                    if (!this.search) return this.customers;
                    const term = this.search.toLowerCase();
                    return this.customers.filter(c => (c.name || '').toLowerCase().includes(term));
                },

                select(cust) {
                    this.selectedCustomer = cust.id;
                    this.selectedName = cust.name;
                    this.open = false;
                }
            };
        }
    </script>

</x-admin-layout>
