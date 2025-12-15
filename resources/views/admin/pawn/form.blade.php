<x-admin-layout :title="$isEdit ? 'Edit Pawn Item' : 'Add Pawn Item'">

    {{-- PRINT STYLES --}}
    <style>
        @media print {
            /* Hide everything by default */
            body * {
                visibility: hidden;
            }
            /* Show only the receipt */
            #printable-receipt, #printable-receipt * {
                visibility: visible;
            }
            /* Position receipt at the top */
            #printable-receipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 0;
                background: white;
            }
            /* Hide buttons and navigation */
            .no-print {
                display: none !important;
            }
        }
    </style>

    <div class="w-full px-4 sm:px-6 py-6 no-print">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-200 pb-4 mb-6 gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-gray-900 tracking-tight">
                    {{ $isEdit ? 'Edit Pawn Item' : 'Add Pawn Item' }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $isEdit ? 'Update details or print receipt.' : 'Create a new pawn entry.' }}
                </p>
            </div>

            <a href="{{ route('admin.pawn.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                ← Back
            </a>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm" role="alert">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Please fix the following:
                </div>
                <ul class="list-disc ml-5 space-y-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ $isEdit ? route('admin.pawn.update', $pawnItem) : route('admin.pawn.store') }}"
            enctype="multipart/form-data"
            class="space-y-6"
            x-data="{
                tab: '{{ old('customer_mode', 'existing') }}',
                price: '{{ old('price', $pawnItem->price ?? '') }}',
                interest: '{{ old('interest_cost', $pawnItem->interest_cost ?? '') }}',

                // Function to compute 3% interest
                computeInterest() {
                    let p = parseFloat(this.price);
                    if(!isNaN(p) && p > 0) {
                        this.interest = (p * 0.03).toFixed(2);
                    } else {
                        this.interest = '';
                    }
                }
            }"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <input type="hidden" name="customer_mode" :value="tab">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- LEFT COLUMN: CUSTOMER & PAWN DETAILS --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Customer Card --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-5">
                        <div class="flex items-center justify-between mb-5 border-b border-gray-50 pb-3">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-800 flex items-center gap-2">
                                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Customer Information
                            </h2>

                            <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1">
                                <button type="button"
                                        class="px-3 py-1 text-xs font-bold rounded-md transition-all"
                                        :class="tab === 'existing' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700'"
                                        @click="tab='existing'">
                                    Existing
                                </button>
                                <button type="button"
                                        class="px-3 py-1 text-xs font-bold rounded-md transition-all"
                                        :class="tab === 'new' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700'"
                                        @click="tab='new'">
                                    Register New
                                </button>
                            </div>
                        </div>

                        {{-- Existing Customer --}}
                        <div x-show="tab === 'existing'" x-transition>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                Search Customer <span class="text-red-500">*</span>
                            </label>
                            <select name="customer_id"
                                    :required="tab === 'existing'"
                                    class="w-full rounded-xl border-gray-200 bg-gray-50 py-2.5 px-3 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                <option value="">-- Select a customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                        @selected(old('customer_id', $pawnItem->customer_id) == $c->id)>
                                        {{ $c->name }} — {{ $c->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- New Customer --}}
                        <div x-show="tab === 'new'" x-transition class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                                       :required="tab === 'new'"
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 py-2 px-3 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                                       placeholder="Enter full name">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Email
                                </label>
                                <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 py-2 px-3 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                                       placeholder="email@example.com">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Phone (Optional)
                                </label>
                                <input type="text" name="customer_contact_no" value="{{ old('customer_contact_no') }}"
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 py-2 px-3 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                                       placeholder="09xxxxxxxxx">
                            </div>
                        </div>
                    </div>

                    {{-- Pawn Details Card --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-5">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-800 mb-5 border-b border-gray-50 pb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            Pawn Item Details
                        </h2>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Item Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="title" value="{{ old('title', $pawnItem->title) }}"
                                       required
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 py-2 px-3 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                                       placeholder="e.g., 18K Gold Ring with Diamond">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Description
                                </label>
                                <textarea name="description" rows="3"
                                          class="w-full rounded-xl border-gray-200 bg-gray-50 py-2 px-3 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                                          placeholder="Weight, karats, condition, etc...">{{ old('description', $pawnItem->description) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Principal Amount <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">₱</span>
                                    </div>
                                    <input type="number" step="0.01" name="price"
                                           x-model="price"
                                           @input="computeInterest()"
                                           required
                                           class="w-full rounded-xl border-gray-200 bg-gray-50 py-2 pl-7 pr-3 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500 font-bold text-gray-900"
                                           placeholder="0.00">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Interest (3%)
                                </label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">₱</span>
                                    </div>
                                    <input type="number" step="0.01" name="interest_cost"
                                           x-model="interest"
                                           class="w-full rounded-xl border-gray-200 bg-gray-50 py-2 pl-7 pr-3 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/50"
                                           placeholder="0.00">
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Auto-calculated (can be overridden)</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Due Date (Default: +3 Months)
                                </label>
                                <input type="date" name="due_date"
                                       value="{{ old('due_date', $pawnItem->due_date ? $pawnItem->due_date->format('Y-m-d') : date('Y-m-d', strtotime('+3 months'))) }}"
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 py-2 px-3 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">
                                    Status
                                </label>
                                <select name="status"
                                        class="w-full rounded-xl border-gray-200 bg-gray-50 py-2 px-3 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                    @foreach(['active' => 'Active', 'redeemed' => 'Redeemed', 'forfeited' => 'Forfeited'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('status', $pawnItem->status ?: 'active') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN: IMAGES & ACTION --}}
                <div class="space-y-6">

                    {{-- Actions --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-5 flex flex-col gap-3">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-3 text-sm font-bold text-white shadow-lg hover:to-yellow-700 transition transform active:scale-[0.98]">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            {{ $isEdit ? 'Update Pawn Item' : 'Save Pawn Item' }}
                        </button>

                        @if($isEdit)
                            <button type="button"
                                    onclick="window.print()"
                                    class="w-full inline-flex items-center justify-center rounded-xl border-2 border-gray-900 bg-white px-6 py-3 text-sm font-bold text-gray-900 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Print Receipt
                            </button>
                        @endif
                    </div>

                    {{-- Images Card --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-5">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-800 mb-5 border-b border-gray-50 pb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Images
                        </h2>

                        @if($isEdit && ($pawnItem->relationLoaded('pictures') ? $pawnItem->pictures->count() : $pawnItem->pictures()->count()))
                            <div class="mb-5">
                                <p class="text-xs font-bold text-gray-500 mb-2 uppercase">Existing</p>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach(($pawnItem->relationLoaded('pictures') ? $pawnItem->pictures : $pawnItem->pictures()->get()) as $pic)
                                        <div class="relative group rounded-lg overflow-hidden border border-gray-200">
                                            <img src="{{ asset('storage/' . ltrim($pic->url, '/')) }}" class="w-full h-24 object-cover">
                                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                                <label class="flex items-center gap-1 cursor-pointer text-white text-xs">
                                                    <input type="checkbox" name="remove_images[]" value="{{ $pic->id }}" class="rounded text-red-600 focus:ring-red-600">
                                                    <span>Remove</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">
                                Upload New
                            </label>
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    <p class="text-xs text-gray-500">Click to upload images</p>
                                </div>
                                <input type="file" name="images[]" multiple class="hidden" />
                            </label>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

    {{-- HIDDEN PRINT RECEIPT TEMPLATE --}}
    @if($isEdit)
        <div id="printable-receipt" class="hidden p-8 font-serif text-black bg-white">
            {{-- Header --}}
            <div class="text-center mb-6 border-b-2 border-black pb-4">
                <h1 class="text-3xl font-bold tracking-widest uppercase">AUAG Jewelry</h1>
                <p class="text-sm">Official Pawnshop & Jewelry</p>
                <p class="text-xs mt-1">123 Gold Street, Business District, City</p>
                <h2 class="text-xl font-bold mt-4 uppercase border-2 border-black inline-block px-4 py-1">Pawn Ticket</h2>
            </div>

            {{-- Details Grid --}}
            <div class="flex justify-between items-start mb-6">
                <div class="w-1/2">
                    <p class="text-xs font-bold uppercase text-gray-500 mb-1">Customer Details</p>
                    <p class="font-bold text-lg">{{ $pawnItem->customer->name ?? 'N/A' }}</p>
                    <p class="text-sm">{{ $pawnItem->customer->email ?? '' }}</p>
                    <p class="text-sm">{{ $pawnItem->customer->contact_no ?? '' }}</p>
                </div>
                <div class="w-1/2 text-right">
                    <p class="text-xs font-bold uppercase text-gray-500 mb-1">Ticket Reference</p>
                    <p class="font-bold text-lg">#{{ str_pad($pawnItem->id, 6, '0', STR_PAD_LEFT) }}</p>
                    <p class="text-sm">Date: {{ $pawnItem->created_at->format('M d, Y') }}</p>
                    <p class="text-sm font-bold text-red-600">Due: {{ optional($pawnItem->due_date)->format('M d, Y') }}</p>
                </div>
            </div>

            {{-- Item Table --}}
            <div class="border-2 border-black mb-6">
                <div class="bg-gray-100 border-b border-black p-2 font-bold flex justify-between uppercase text-xs">
                    <span>Item Description</span>
                    <span>Valuation</span>
                </div>
                <div class="p-4 flex justify-between items-start min-h-[100px]">
                    <div class="w-2/3 pr-4">
                        <p class="font-bold text-lg">{{ $pawnItem->title }}</p>
                        <p class="text-sm mt-1 whitespace-pre-wrap">{{ $pawnItem->description ?? 'No description provided.' }}</p>
                    </div>
                    <div class="w-1/3 text-right">
                        <div class="flex justify-between mb-1">
                            <span class="text-sm">Principal:</span>
                            <span class="font-bold">₱{{ number_format($pawnItem->price, 2) }}</span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm">Interest (3%):</span>
                            <span>₱{{ number_format($pawnItem->interest_cost, 2) }}</span>
                        </div>
                        <div class="border-t border-black mt-2 pt-2 flex justify-between">
                            <span class="font-bold">NET PROCEEDS:</span>
                            {{-- Usually Net Proceeds = Principal - Deductions. Assuming just Principal here for simplicity unless interest is deducted upfront --}}
                            <span class="font-bold text-xl">₱{{ number_format($pawnItem->price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Terms --}}
            <div class="text-[10px] text-justify leading-tight text-gray-600 mb-8">
                <p><strong>TERMS AND CONDITIONS:</strong> This pawn ticket is the receipt for the pledge. The pawner hereby accepts the appraisal of the item/s listed above. The pawnshop agrees to keep the pledged item/s in good condition. If the loan is not renewed or redeemed on or before the maturity date, the item/s will be sold to the public.</p>
            </div>

            {{-- Signatures --}}
            <div class="flex justify-between mt-12 pt-8">
                <div class="text-center w-1/3">
                    <div class="border-t border-black pt-2">
                        <p class="font-bold uppercase">{{ auth()->user()->name ?? 'Authorized Staff' }}</p>
                        <p class="text-xs">Processed By</p>
                    </div>
                </div>
                <div class="text-center w-1/3">
                    <div class="border-t border-black pt-2">
                        <p class="font-bold uppercase">{{ $pawnItem->customer->name ?? 'Customer' }}</p>
                        <p class="text-xs">Signature of Pawner</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

</x-admin-layout>