<x-admin-layout :title="$user->exists ? 'Edit Staff' : 'Create Staff'">
    <form method="POST"
        action="{{ $user->exists ? route('admin.staff.update', $user) : route('admin.staff.store') }}"
        class="flex flex-col gap-6">

        @csrf
        @if($user->exists)
            @method('PUT')
        @endif

        {{-- Action Bar at the top (Matches Customer/Product Bar) --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold font-serif text-gray-900">
                {{ $user->exists ? 'Edit Staff' : 'Create Staff' }}
            </h1>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.staff.index') }}"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                    Cancel
                </a>
                {{-- Save Button styled like 'Add New Product' (yellow) --}}
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-yellow-600 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-yellow-700 transition-colors duration-150">
                    {{ $user->exists ? 'Update Staff' : 'Create Staff' }}
                </button>
            </div>
        </div>

        {{-- Main Form Content Card (Full width, single card) --}}
        <div class="rounded-xl bg-white p-6 shadow-md border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-6">Staff Details</h2>

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
                        {{-- Changed focus color from amber to yellow --}}
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500 transition-colors duration-150">
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
                        {{-- Changed focus color from amber to yellow --}}
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500 transition-colors duration-150">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
    

                {{-- Password (Conditional required and placeholder) --}}
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
                        {{-- Changed focus color from amber to yellow --}}
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500 transition-colors duration-150">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Confirmation (Conditional required and placeholder) --}}
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
                        {{-- Changed focus color from amber to yellow --}}
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500 transition-colors duration-150">
                    @error('password_confirmation')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </form>
</x-admin-layout>