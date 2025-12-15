<x-admin-layout :title="$customer->exists ? 'Edit Customer' : 'Create Customer'">
    <form method="POST"
          action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}"
          class="flex flex-col gap-6">
        @csrf
        @if($customer->exists)
            @method('PUT')
        @endif

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-serif font-bold text-gray-900">
                    {{ $customer->exists ? 'Edit Customer' : 'Create Customer' }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $customer->exists ? 'Update customer details below.' : 'Fill up the form to add a new customer.' }}
                </p>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.customers.index') }}"
                   class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-purple-700 transition-colors">
                    {{ $customer->exists ? 'Update Customer' : 'Create Customer' }}
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                <div class="font-semibold mb-2">Please fix the following:</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-xl bg-white p-6 shadow-md border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-6">Customer Details</h2>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $customer->name) }}"
                           required
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email <span class="text-rose-500">*</span>
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $customer->email) }}"
                           required
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contact No --}}
                <div class="sm:col-span-2">
                    <label for="contact_no" class="block text-sm font-medium text-gray-700 mb-1">
                        Contact No (optional)
                    </label>
                    <input type="text"
                           id="contact_no"
                           name="contact_no"
                           value="{{ old('contact_no', $customer->contact_no) }}"
                           placeholder="e.g. 09xxxxxxxxx"
                           class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    @error('contact_no')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </form>
</x-admin-layout>
