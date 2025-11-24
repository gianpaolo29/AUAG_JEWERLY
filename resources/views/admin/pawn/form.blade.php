@php
    /** @var \App\Models\PawnItem $pawnItem */
    $isEdit = $pawnItem->exists;
    $title  = $isEdit ? "Edit Pawn Item #{$pawnItem->id}" : 'New Pawn Item';

    // --- DUE DATE LOGIC START ---
    $dueValue = old('due_date');

    if (!$dueValue) {
        if ($isEdit && $pawnItem->due_date) {
            // Edit mode: use existing due date
            $dueValue = \Illuminate\Support\Carbon::parse($pawnItem->due_date)->format('Y-m-d');
        } elseif (!$isEdit) {
            // New item: default to 3 months from now
            $dueValue = now()->addMonths(3)->format('Y-m-d');
        }
    }
    // --- DUE DATE LOGIC END ---

    // Existing pictures for edit (id + full url)
    $existingPictures = ($pawnItem->pictures ?? collect())->map(fn($pic) => [
    'id'  => $pic->id,
    'url' => asset('storage/' .$pic->url),
]);
@endphp

<x-admin-layout :title="$title">
    <div class="flex flex-col gap-6"
         x-data="pawnCustomerSelect(
            @js($customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name])),
            {{ old('customer_id', $pawnItem->customer_id) ? (int) old('customer_id', $pawnItem->customer_id) : 'null' }}
         )">

        {{-- HEADER --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Manage pawn item details below.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.pawn.index') }}"
                   class="px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100">
                    Cancel
                </a>
                <button type="submit" form="pawn-item-form"
                        class="px-5 py-2 rounded-lg bg-orange-600 text-white font-semibold hover:bg-orange-700 shadow-lg transition duration-150 ease-in-out">
                    {{ $isEdit ? 'Save Changes' : 'Create Pawn Item' }}
                </button>
            </div>
        </div>

        {{-- ERRORS --}}
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-300 bg-red-50 p-4 text-red-700 text-sm">
                <p class="font-semibold mb-2">Please fix the following:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORM CARD --}}
        <div class="bg-white rounded-xl shadow-xl border border-gray-100 divide-y divide-gray-200">
            <form id="pawn-item-form"
                  method="POST"
                  action="{{ $isEdit ? route('admin.pawn.update', $pawnItem) : route('admin.pawn.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                {{-- SECTION: PAWN DETAILS --}}
                <div class="p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4 -mt-2">Pawn Information</h2>

                    {{-- CUSTOMER SELECT WITH SEARCH --}}
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">
                            Customer <span class="text-red-500">*</span>
                        </label>

                        {{-- Hidden field actually submitted --}}
                        <input type="hidden" name="customer_id" :value="selectedId" required>

                        <div class="relative mt-1">
                            <button type="button"
                                    @click="open = !open"
                                    class="w-full flex items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm hover:border-orange-400 focus:outline-none focus:ring-1 focus:ring-orange-500 transition duration-150">
                                <span class="text-gray-900" x-text="selectedName || 'Select customer'"></span>
                                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.5">
                                    <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>

                            <div x-show="open"
                                 x-transition
                                 @click.outside="open = false"
                                 x-cloak
                                 class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-xl">

                                <div class="p-2 border-b border-gray-100">
                                    <input type="text"
                                           x-model="search"
                                           placeholder="Search customer..."
                                           class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                </div>

                                <ul class="max-h-60 overflow-y-auto text-sm">
                                    <template x-for="c in filteredCustomers" :key="c.id">
                                        <li>
                                            <button type="button"
                                                    @click="selectCustomer(c)"
                                                    class="w-full text-left px-3 py-2 text-gray-800 hover:bg-orange-50/70 transition duration-100">
                                                <span x-text="c.name"></span>
                                            </button>
                                        </li>
                                    </template>

                                    <template x-if="filteredCustomers.length === 0">
                                        <li class="px-3 py-2 text-sm text-gray-400">
                                            No customers found.
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <p class="text-xs text-gray-400 mt-1">
                            Start typing to search and select a customer.
                        </p>
                    </div>

                    {{-- TITLE --}}
                    <div class="space-y-1">
                        <label for="title" class="block text-sm font-medium text-gray-700">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" required
                               value="{{ old('title', $pawnItem->title) }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                               placeholder="e.g. 18k Gold Necklace">
                    </div>

                    {{-- DESCRIPTION --}}
                    <div class="space-y-1">
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Description
                        </label>
                        <textarea id="description" name="description" rows="3"
                                  class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                  placeholder="Details about the item (brand, weight, specs)...">{{ old('description', $pawnItem->description) }}</textarea>
                    </div>

                    {{-- PRICE (Principal) + BASE INTEREST --}}
                    <div class="grid md:grid-cols-2 gap-4">
                        {{-- PRICE / PRINCIPAL --}}
                        <div class="space-y-1">
                            <label for="price" class="block text-sm font-medium text-gray-700">
                                Principal (Price) (₱) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                                <input type="number" step="0.01" min="0" required
                                       id="price" name="price"
                                       value="{{ old('price', $pawnItem->price) }}"
                                       class="block w-full rounded-lg border-gray-300 pl-7 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                       placeholder="0.00">
                            </div>
                        </div>

                        {{-- BASE INTEREST --}}
                        <div class="space-y-1">
                            <label for="interest_cost" class="block text-sm font-medium text-gray-700">
                                Base Interest (₱) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₱</span>
                                <input type="number" step="0.01" min="0" required
                                       id="interest_cost" name="interest_cost"
                                       value="{{ old('interest_cost', $pawnItem->interest_cost ?? 0) }}"
                                       class="block w-full rounded-lg border-gray-300 pl-7 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                       placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    {{-- DUE DATE + STATUS --}}
                    <div class="grid md:grid-cols-2 gap-4">
                        {{-- DUE DATE --}}
                        <div class="space-y-1">
                            <label for="due_date" class="block text-sm font-medium text-gray-700">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="due_date" name="due_date" required
                                   value="{{ old('due_date', $dueValue) }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            <p class="text-xs text-gray-400 mt-1">
                                Default value is 3 months from today.
                            </p>
                        </div>

                        {{-- STATUS --}}
                        <div class="space-y-1">
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Status
                            </label>
                            @php
                                $currentStatus = old('status', $pawnItem->status ?? 'active');
                            @endphp
                            <select id="status" name="status"
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                <option value="active"    @selected($currentStatus === 'active')>Active</option>
                                <option value="redeemed"  @selected($currentStatus === 'redeemed')>Redeemed</option>
                                <option value="forfeited" @selected($currentStatus === 'forfeited')>Forfeited</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- SECTION: PICTURES --}}
                <div class="p-6 space-y-4"
                     x-data="pawnPictures(@js($existingPictures))">
                    <h2 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">Item Pictures</h2>

                    <p class="text-sm text-gray-500">
                        Upload images of the pawned item to help identify it. You can add multiple pictures.
                    </p>

                    {{-- FILE INPUT (hidden) --}}
                    <input type="file"
                           x-ref="fileInput"
                           name="images[]"
                           multiple
                           accept="image/*"
                           class="hidden"
                           @change="handleFiles($event)">

                    <button type="button"
                            @click="$refs.fileInput.click()"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border-2 border-dashed border-gray-300 text-sm font-medium text-gray-700 hover:border-orange-400 hover:bg-orange-50/20 transition duration-150">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Add Pictures
                    </button>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 pt-2">
                        {{-- EXISTING PICS --}}
                        <template x-for="pic in existing" :key="pic.id">
                            <div class="relative group aspect-square"
                                 :class="{ 'opacity-50 ring-2 ring-red-500': pic.remove }">
                                <img :src="pic.url"
                                     class="w-full h-full object-cover rounded-lg border border-gray-200 transition duration-150"
                                     alt="Existing picture">
                                <button type="button"
                                        @click="toggleRemoveExisting(pic.id)"
                                        class="absolute top-1 right-1 size-7 flex items-center justify-center bg-white/90 border border-gray-300 rounded-full text-xs text-gray-700 shadow-md transition duration-150 hover:scale-105"
                                        :class="{ 'bg-red-600 text-white hover:bg-red-700': pic.remove, 'hover:bg-red-500 hover:text-white': !pic.remove }">
                                    <span x-show="!pic.remove" class="text-base font-semibold">✕</span>
                                    <svg x-show="pic.remove" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 12a9 9 0 0 1-9 9 9 9 0 0 1-9-9 9 9 0 0 1 9-9c1.69 0 3.32.74 4.51 2.05L18 8"></path>
                                        <path d="M14 4v5h5"></path>
                                    </svg>
                                </button>

                                {{-- Hidden fields to tell backend keep/remove --}}
                                <template x-if="!pic.remove">
                                    <input type="hidden"
                                                 name="keep_images[]"
                                                 :value="pic.id"
                                                 >
                                </template>

                                <template x-if="pic.remove">
                                    <input type="hidden"
                                           name="remove_images[]"
                                           :value="pic.id"
                                           x-if="pic.remove">
                                </template>

                            </div>
                        </template>

                        {{-- NEW PICS PREVIEW --}}
                        <template x-for="(img, index) in previews" :key="index">
                            <div class="relative group aspect-square">
                                <img :src="img.url"
                                     class="w-full h-full object-cover rounded-lg border-2 border-orange-400"
                                     alt="New picture preview">
                                <button type="button"
                                        @click="removeNew(index)"
                                        class="absolute top-1 right-1 size-7 flex items-center justify-center bg-white/90 border border-gray-300 rounded-full text-xs text-gray-700 shadow-md transition duration-150 hover:bg-red-500 hover:text-white hover:scale-105">
                                    <span class="text-base font-semibold">✕</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script>
        function pawnCustomerSelect(customers, initialId = null) {
            return {
                customers: customers || [],
                search: '',
                open: false,
                selectedId: initialId ?? null,

                get selectedName() {
                    const found = this.customers.find(c => c.id === this.selectedId);
                    return found ? found.name : '';
                },

                get filteredCustomers() {
                    if (!this.search) return this.customers;
                    const term = this.search.toLowerCase();
                    return this.customers.filter(c =>
                        (c.name || '').toLowerCase().includes(term)
                    );
                },

                selectCustomer(c) {
                    this.selectedId = c.id;
                    this.search = '';
                    this.open = false;
                },
            };
        }

        function pawnPictures(existingPictures) {
            return {
                existing: (existingPictures || []).map(p => ({
                    id: p.id,
                    url: p.url,
                    remove: false,
                })),
                previews: [],  // { url, file }

                handleFiles(event) {
                    const files = Array.from(event.target.files || []);
                    this.previews = files.map(file => ({
                        file,
                        url: URL.createObjectURL(file),
                    }));
                    this.syncFileInput();
                },

                removeNew(index) {
                    this.previews.splice(index, 1);
                    this.syncFileInput();
                },

                syncFileInput() {
                    const dt = new DataTransfer();
                    this.previews.forEach(p => dt.items.add(p.file));
                    this.$refs.fileInput.files = dt.files;
                },

                toggleRemoveExisting(id) {
                    const pic = this.existing.find(p => p.id === id);
                    if (pic) {
                        pic.remove = !pic.remove;
                    }
                },
            };
        }
    </script>
</x-admin-layout>
