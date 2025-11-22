<x-admin-layout :title="$user->exists ? 'Edit Customer' : 'Create Customer'">
    <form method="POST"
        action="{{ $user->exists ? route('admin.customers.update', $user) : route('admin.customers.store') }}"
        class="flex flex-col gap-6">

        @csrf
        @if($user->exists)
            @method('PUT')
        @endif

        {{-- Action Bar at the top (Styled like the Product Management Action Bar) --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $user->exists ? 'Edit Customer' : 'Create Customer' }}
            </h1>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.customers.index') }}"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                    Cancel
                </a>
                {{-- Save Button styled like 'Add New Product' --}}
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-purple-700 transition-colors duration-150">
                    {{ $user->exists ? 'Update Customer' : 'Create Customer' }}
                </button>
            </div>
        </div>
        
        <p class="text-sm text-gray-500 -mt-4">
             {{ $user->exists ? 'Update customer details below.' : 'Fill up the form to add a new customer.' }}
        </p>
        
        {{-- Main Form Content Card (Now full width since the image card is gone) --}}
        {{-- Removed 'grid grid-cols-1 lg:grid-cols-3 gap-6' and retained the single card structure --}}
        <div class="rounded-xl bg-white p-6 shadow-md border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-6">Customer Details</h2>

            {{-- Fields are now within a single container, still using a 2-column grid --}}
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                
                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500 transition-colors duration-150">
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
                        value="{{ old('email', $user->email) }}"
                        required
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500 transition-colors duration-150">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password 
                        @if(!$user->exists)
                            <span class="text-rose-500">*</span>
                        @endif
                    </label>
                    <input type="password"
                        id="password"
                        name="password"
                        placeholder="{{ $user->exists ? 'Leave blank to keep current' : '' }}"
                        @if(!$user->exists) required @endif
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500 transition-colors duration-150">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Confirmation --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirm Password 
                        @if(!$user->exists)
                            <span class="text-rose-500">*</span>
                        @endif
                    </label>
                    <input type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="{{ $user->exists ? 'Leave blank if not changing' : '' }}"
                        @if(!$user->exists) required @endif
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500 transition-colors duration-150">
                    @error('password_confirmation')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
    </form>
</x-admin-layout>