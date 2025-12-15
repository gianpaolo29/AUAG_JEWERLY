<x-staff-layout :title="$isEdit ? 'Edit Repair' : 'New Repair'">

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
         x-data="repairForm({
            customers: @js($customers->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'email' => $c->email,
                'contact_no' => $c->contact_no,
            ])),
            mode: @js(old('customer_mode', $repair->customer_id ? 'existing' : 'existing')),
            selectedCustomer: {{ old('customer_id', $repair->customer_id) ? (int) old('customer_id', $repair->customer_id) : 'null' }},
            existingImage: @js($isEdit && $repair->picture ? asset('storage/'.$repair->picture->url) : '')
         })"
         x-init="init()">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 border-b border-gray-200 pb-6">
            <div>
                <h1 class="text-3xl font-serif font-bold text-gray-900 tracking-tight">
                    {{ $isEdit ? 'Edit Repair Ticket' : 'New Repair Ticket' }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $isEdit ? 'Update existing repair details' : 'Create a new service request' }}
                </p>
            </div>
            <a href="{{ route('staff.repairs.index') }}"
               class="mt-4 sm:mt-0 px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition shadow-sm">
                ← Back to List
            </a>
        </div>

        {{-- ERROR ALERT --}}
        @if ($errors->any())
            <div class="mb-8 bg-red-50 border border-red-100 rounded-xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <h3 class="text-sm font-bold text-red-800">Please fix the following errors:</h3>
                    <ul class="mt-1 list-disc list-inside text-sm text-red-600">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="repair-form"
              method="POST"
              action="{{ $isEdit ? route('staff.repairs.update', $repair) : route('staff.repairs.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                {{-- LEFT COLUMN: CUSTOMER INFO --}}
                <div class="md:col-span-2 space-y-8">
                    
                    {{-- CUSTOMER CARD --}}
                    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.05)] border border-gray-100 p-6">
                        <h2 class="text-lg font-serif font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-2">Customer Details</h2>

                        {{-- TABS --}}
                        <div class="flex gap-4 mb-6">
                            <button type="button" @click="setMode('existing')"
                                    class="px-5 py-2 text-sm font-medium rounded-full transition-all border"
                                    :class="mode === 'existing' ? 'bg-gray-900 text-white border-gray-900 shadow-md' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'">
                                Existing Customer
                            </button>
                            <button type="button" @click="setMode('new')"
                                    class="px-5 py-2 text-sm font-medium rounded-full transition-all border"
                                    :class="mode === 'new' ? 'bg-gray-900 text-white border-gray-900 shadow-md' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'">
                                Register New
                            </button>
                        </div>

                        <input type="hidden" name="customer_mode" :value="mode">

                        {{-- EXISTING CUSTOMER SEARCH --}}
                        <div x-show="mode === 'existing'" x-transition>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                                Search Customer <span class="text-red-500">*</span>
                            </label>
                            <input type="hidden" name="customer_id" x-model="selectedCustomer">

                            <div class="relative">
                                <button type="button" @click="open = !open"
                                        class="w-full text-left bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-yellow-500 focus:border-yellow-500 block p-3.5 shadow-sm flex justify-between items-center">
                                    <span x-text="selectedLabel || 'Select a customer...'"></span>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>

                                {{-- DROPDOWN --}}
                                <div x-show="open" x-cloak @click.outside="open = false"
                                     class="absolute z-10 mt-2 w-full max-h-60 overflow-y-auto bg-white border border-gray-100 rounded-xl shadow-xl">
                                    <div class="sticky top-0 bg-white p-2 border-b border-gray-100">
                                        <input type="text" x-model="search" placeholder="Type name to filter..."
                                               class="w-full bg-gray-50 border border-gray-200 rounded-lg text-sm px-3 py-2 focus:ring-yellow-500 focus:border-yellow-500">
                                    </div>
                                    <template x-for="cust in filtered" :key="cust.id">
                                        <button type="button" @click="select(cust)"
                                                class="w-full text-left px-4 py-3 hover:bg-yellow-50 border-b border-gray-50 last:border-0 transition">
                                            <div class="text-sm font-bold text-gray-800" x-text="cust.name"></div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                <span x-text="cust.email"></span> • <span x-text="cust.contact_no"></span>
                                            </div>
                                        </button>
                                    </template>
                                    <div x-show="filtered.length === 0" class="p-4 text-sm text-gray-500 text-center">No customers found.</div>
                                </div>
                            </div>
                        </div>

                        {{-- NEW CUSTOMER FIELDS --}}
                        <div x-show="mode === 'new'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                                       :required="mode === 'new'"
                                       class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                                       :required="mode === 'new'"
                                       class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                                       :required="mode === 'new'"
                                       class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                            </div>
                        </div>
                    </div>

                    {{-- REPAIR DETAILS CARD --}}
                    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.05)] border border-gray-100 p-6">
                        <h2 class="text-lg font-serif font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-2">Repair Information</h2>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                                    Description of Issue <span class="text-red-500">*</span>
                                </label>
                                <textarea name="description" rows="4" required
                                          class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3"
                                          placeholder="Describe the jewelry item and the service required...">{{ old('description', $repair->description) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                                        Estimated Price (₱) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">₱</span>
                                        </div>
                                        <input type="number" name="price" step="0.01" value="{{ old('price', $repair->price) }}" required
                                               class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3 pl-8">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Status</label>
                                    <select name="status" class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                                        <option value="pending"   @selected(old('status', $repair->status ?? 'pending')=='pending')>Pending</option>
                                        <option value="completed" @selected(old('status', $repair->status)=='completed')>Completed</option>
                                        <option value="cancelled" @selected(old('status', $repair->status)=='cancelled')>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN: IMAGE & ACTION --}}
                <div class="space-y-8">
                    
                    {{-- IMAGE CARD --}}
                    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.05)] border border-gray-100 p-6">
                        <h2 class="text-lg font-serif font-semibold text-gray-800 mb-4">Item Photo</h2>
                        
                        <div class="flex flex-col items-center justify-center">
                            <template x-if="preview">
                                <div class="relative w-full aspect-square rounded-xl overflow-hidden border border-gray-200 mb-4 group">
                                    <img :src="preview" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                </div>
                            </template>
                            
                            <template x-if="!preview">
                                <div class="w-full aspect-square rounded-xl bg-gray-50 border-2 border-dashed border-gray-200 flex flex-col items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span class="text-xs text-gray-400">No image uploaded</span>
                                </div>
                            </template>

                            <label class="w-full">
                                <span class="block w-full text-center px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer shadow-sm transition">
                                    {{ $isEdit ? 'Change Photo' : 'Upload Photo' }}
                                </span>
                                <input type="file" name="image" accept="image/*" class="hidden"
                                       @change="const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f);">
                            </label>
                        </div>
                    </div>

                    {{-- SUBMIT --}}
                    <button type="submit" 
                            class="w-full py-4 bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-xl hover:to-gray-700 shadow-lg font-bold tracking-wide transition-all transform active:scale-[0.98]">
                        {{ $isEdit ? 'Save Changes' : 'Create Ticket' }}
                    </button>

                </div>
            </div>
        </form>
    </div>

    <script>
        function repairForm({ customers, mode, selectedCustomer, existingImage }) {
            return {
                customers,
                mode,
                selectedCustomer,
                selectedLabel: '',
                search: '',
                open: false,
                preview: existingImage,

                init() {
                    if (this.selectedCustomer) {
                        const c = this.customers.find(x => Number(x.id) === Number(this.selectedCustomer));
                        this.selectedLabel = c ? c.name : '';
                    }
                },

                setMode(m) {
                    this.mode = m;
                    this.open = false;
                    this.search = '';
                    if (m === 'new') {
                        this.selectedCustomer = null;
                        this.selectedLabel = '';
                    }
                },

                get filtered() {
                    const term = (this.search || '').toLowerCase().trim();
                    if (!term) return this.customers;
                    return this.customers.filter(c =>
                        (c.name || '').toLowerCase().includes(term) ||
                        (c.email || '').toLowerCase().includes(term) ||
                        (c.contact_no || '').toLowerCase().includes(term)
                    );
                },

                select(cust) {
                    this.selectedCustomer = Number(cust.id);
                    this.selectedLabel = cust.name;
                    this.open = false;
                }
            };
        }
    </script>

</x-staff-layout>