<x-staff-layout title="New Pawn Item">

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
         x-data="pawnForm()"
         x-init="init()">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 border-b border-gray-200 pb-6">
            <div>
                <h1 class="text-3xl font-serif font-bold text-gray-900 tracking-tight">Record Pawn Item</h1>
                <p class="text-sm text-gray-500 mt-1">Create a new pawn ticket for a customer.</p>
            </div>
            <a href="{{ route('staff.pawn.index') }}"
               class="mt-4 sm:mt-0 px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition shadow-sm">
                ← Back to List
            </a>
        </div>

        <form action="{{ route('staff.pawn.store') }}"
              method="POST"
              enctype="multipart/form-data"
              @submit.prevent="validateAndSubmit($event)">
            @csrf

            {{-- CUSTOMER SECTION --}}
            <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.05)] border border-gray-100 p-6 mb-8">
                <h2 class="text-lg font-serif font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-2">
                    Customer Details
                </h2>

                {{-- Tabs --}}
                <div class="flex gap-4 mb-6">
                    <button type="button"
                            @click="switchTab('existing')"
                            class="px-5 py-2 text-sm font-medium rounded-full transition-all border"
                            :class="customerTab === 'existing'
                                ? 'bg-gray-900 text-white border-gray-900 shadow-md'
                                : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'">
                        Select Existing
                    </button>

                    <button type="button"
                            @click="switchTab('new')"
                            class="px-5 py-2 text-sm font-medium rounded-full transition-all border"
                            :class="customerTab === 'new'
                                ? 'bg-gray-900 text-white border-gray-900 shadow-md'
                                : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'">
                        Register New
                    </button>
                </div>

                {{-- MODE --}}
                <input type="hidden" name="customer_mode" :value="customerTab">

                {{-- EXISTING CUSTOMER --}}
                <div x-show="customerTab === 'existing'" x-transition>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                        Find Customer <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <input type="text"
                               x-model="customerSearch"
                               placeholder="Type to search name, email, or phone..."
                               class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-yellow-500 focus:border-yellow-500 block p-3.5 shadow-sm">

                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Dropdown Results --}}
                    <div x-show="customerSearch.length > 0 && selectedCustomerName !== customerSearch"
                         class="mt-2 max-h-56 overflow-y-auto border border-gray-100 rounded-xl bg-white shadow-xl z-10">
                        <template x-for="c in filteredCustomers()" :key="c.id">
                            <button type="button"
                                    @click="selectCustomer(c)"
                                    class="w-full text-left px-4 py-3 flex flex-col hover:bg-yellow-50 border-b border-gray-50 last:border-0 transition">
                                <span class="text-sm font-bold text-gray-800" x-text="c.name"></span>
                                <div class="flex gap-2 text-xs text-gray-500 mt-0.5">
                                    <span x-text="c.email || ''"></span>
                                    <span x-text="c.contact_no || ''"></span>
                                </div>
                            </button>
                        </template>

                        <template x-if="filteredCustomers().length === 0">
                            <div class="p-4 text-sm text-gray-500 text-center">No customers found.</div>
                        </template>
                    </div>

                    {{-- Selected Customer ID submitted --}}
                    <input type="hidden"
                           name="customer_id"
                           :value="selectedCustomerId"
                           x-bind:disabled="customerTab !== 'existing'">
                </div>

                {{-- NEW CUSTOMER --}}
                <div x-show="customerTab === 'new'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="customer_name"
                               value="{{ old('customer_name') }}"
                               x-bind:disabled="customerTab !== 'new'"
                               x-bind:required="customerTab === 'new'"
                               class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                            Email <span class="text-[11px] font-normal text-gray-400">(optional)</span>
                        </label>
                        <input type="email"
                               name="customer_email"
                               value="{{ old('customer_email') }}"
                               x-bind:disabled="customerTab !== 'new'"
                               class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                            Phone <span class="text-[11px] font-normal text-gray-400">(optional)</span>
                        </label>
                        <input type="text"
                               name="customer_phone"
                               value="{{ old('customer_phone') }}"
                               x-bind:disabled="customerTab !== 'new'"
                               class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                    </div>
                </div>
            </div>

            @php
                $defaultLoanDate = old('loan_date', now()->format('Y-m-d'));
                $defaultDueDate  = old('due_date', now()->addMonths(3)->format('Y-m-d'));
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- LEFT: PAWN DETAILS --}}
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-[0_4px_20px_rgb(0,0,0,0.05)] border border-gray-100 p-6">
                    <h2 class="text-lg font-serif font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-2">
                        Pawn Details
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Loan Date --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                                Date Loan Granted <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                   name="loan_date"
                                   x-model="loanDate"
                                   @change="updateDueDate()"
                                   value="{{ $defaultLoanDate }}"
                                   required
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                        </div>

                        {{-- Maturity --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                                Maturity Date (3 months) <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                   name="due_date"
                                   x-model="dueDate"
                                   value="{{ $defaultDueDate }}"
                                   required
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                        </div>

                        {{-- Item Title --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                                Item Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3"
                                   placeholder="Ex: 18k Gold Necklace with Pendant">
                        </div>

                        {{-- Description --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                                Description of the Pawn <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description"
                                      rows="3"
                                      required
                                      class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3"
                                      placeholder="Weight, karat, markings, brand, etc.">{{ old('description') }}</textarea>
                        </div>

                        {{-- Images --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                                Item Pictures
                            </label>
                            <input type="file"
                                   name="images[]"
                                   multiple
                                   accept="image/*"
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3">
                            <p class="mt-1 text-[11px] text-gray-400">
                                Upload clear photos of the jewelry item for reference.
                            </p>
                        </div>

                    </div>
                </div>

                {{-- RIGHT: FINANCIAL SUMMARY --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-gray-100 p-6 sticky top-24">
                        <h2 class="text-lg font-serif font-semibold text-gray-800 mb-4">
                            Loan & Charges
                        </h2>

                        <div class="space-y-4 mb-6">

                            {{-- Principal --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Principal Amount (Loan) <span class="text-red-500">*</span>
                                </label>
                                <input type="number"
                                       name="price"
                                       step="0.01"
                                       min="1"
                                       x-model.number="principal"
                                       required
                                       class="w-full bg-gray-50 border border-gray-200 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 p-3"
                                       placeholder="Ex: 6400.00">
                            </div>

                            {{-- Interest --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Interest (3% of Principal)
                                </label>
                                <input type="text"
                                       :value="money(interestAmount())"
                                       class="w-full bg-gray-100 border border-gray-200 rounded-xl p-3 text-gray-700"
                                       readonly>

                                <input type="hidden"
                                       name="interest_cost"
                                       :value="interestAmount().toFixed(2)">
                            </div>

                        </div>

                        {{-- Summary --}}
                        <div class="border-t border-gray-100 pt-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Principal</span>
                                <span class="font-medium" x-text="money(principal || 0)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Interest (3%)</span>
                                <span class="font-medium" x-text="money(interestAmount())"></span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-dashed border-gray-200 mt-2">
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Total Due
                                </span>
                                <span class="text-xl font-serif font-bold text-gray-900" x-text="money(totalDue())"></span>
                            </div>
                        </div>

                        <button type="submit"
                                class="mt-6 w-full py-3.5 bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-xl hover:to-gray-700 shadow-lg transition-all transform active:scale-[0.98] font-bold tracking-wide">
                            Save Pawn Ticket
                        </button>
                    </div>
                </div>

            </div>
        </form>

        {{-- ERROR MODAL --}}
        <div x-cloak x-show="errorOpen"
             class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="errorOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="errorOpen"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         @click.outside="errorOpen = false"
                         class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-sm border border-red-100">

                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="mx-auto flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-red-50 border-4 border-red-50 mb-4 animate-bounce">
                                    <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>

                                <h3 class="text-xl font-serif font-bold leading-6 text-gray-900" id="modal-title">
                                    Attention Needed
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500" x-text="errorMessage"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse justify-center">
                            <button type="button"
                                    @click="errorOpen = false"
                                    class="inline-flex w-full justify-center rounded-xl bg-red-600 px-8 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto transition-colors">
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function pawnForm() {
            return {
                customers: @js($customers),

                customerTab: 'existing',
                customerSearch: '',
                selectedCustomerId: null,
                selectedCustomerName: '',

                loanDate: @js($defaultLoanDate ?? now()->format('Y-m-d')),
                dueDate: @js($defaultDueDate ?? now()->addMonths(3)->format('Y-m-d')),

                principal: Number(@js(old('price', 0))) || 0,

                errorOpen: false,
                errorMessage: '',

                init() {
                    @if($errors->any())
                        this.showError(@js($errors->first()));
                    @endif
                },

                switchTab(tab) {
                    this.customerTab = tab;

                    if (tab === 'new') {
                        this.selectedCustomerId = null;
                        this.selectedCustomerName = '';
                        this.customerSearch = '';
                    }
                },

                validateAndSubmit(e) {
                    const form = e.target;

                    if (this.customerTab === 'existing') {
                        if (!this.selectedCustomerId) {
                            this.showError('Please select an existing customer.');
                            return;
                        }
                    } else {
                        const name  = (form.querySelector('[name="customer_name"]')?.value || '').trim();
                        if (!name) {
                            this.showError('Please enter the customer name to register a new customer.');
                            return;
                        }
                    }

                    if (!this.principal || Number(this.principal) <= 0) {
                        this.showError('Please enter a valid principal amount.');
                        return;
                    }

                    form.submit();
                },

                showError(msg) {
                    this.errorMessage = msg;
                    this.errorOpen = true;
                },

                filteredCustomers() {
                    const s = (this.customerSearch || '').toLowerCase().trim();
                    if (!s) return this.customers;

                    return this.customers.filter(c =>
                        ((c.name || '').toLowerCase().includes(s)) ||
                        ((c.email || '').toLowerCase().includes(s)) ||
                        ((c.contact_no || '').toLowerCase().includes(s))
                    );
                },

                selectCustomer(c) {
                    this.selectedCustomerId = Number(c.id);
                    this.selectedCustomerName = c.name;
                    this.customerSearch = c.name;
                },

                updateDueDate() {
                    if (!this.loanDate) return;
                    const d = new Date(this.loanDate);
                    if (isNaN(d.getTime())) return;
                    d.setMonth(d.getMonth() + 3);
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    this.dueDate = `${y}-${m}-${day}`;
                },

                interestAmount() {
                    return Number(this.principal || 0) * 0.03;
                },

                totalDue() {
                    return Number(this.principal || 0) + this.interestAmount();
                },

                money(v) {
                    return '₱' + Number(v || 0).toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    });
                },
            }
        }
    </script>

</x-staff-layout>
