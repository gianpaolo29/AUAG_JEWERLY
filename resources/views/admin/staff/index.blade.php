<x-admin-layout title="Staff Management">
    <div class="flex flex-col gap-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-serif font-bold text-gray-900">Staff</h1>
                <p class="text-sm text-gray-500">Manage staff accounts.</p>
            </div>

            {{-- ADD STAFF BUTTON: Changed color from amber to orange (like 'Add New Product') --}}
            <a href="{{ route('admin.staff.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-yellow-500 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-yellow-600 transition-colors duration-150">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                </svg>
                Add Staff
            </a>
        </div>

        {{-- Search --}}
        <div class="rounded-xl bg-white p-4 shadow border border-gray-100">
            <form method="GET" class="flex flex-col gap-3 md:flex-row md:items-center">
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 19l-6-6M5 11a6 6 0 1 1 12 0 6 6 0 0 1-12 0z" stroke-linecap="round" />
                    </svg>
                    <input type="search"
                            name="q"
                            value="{{ $q }}"
                            placeholder="Search staff by name or email…"
                            {{-- Changed focus color from amber to yellow --}}
                            class="w-full rounded-lg border-gray-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                </div>
                <div class="flex gap-2 md:ml-auto">
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-black">
                        Search
                    </button>
                    <a href="{{ route('admin.staff.index') }}"
                       class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-xl bg-white shadow border border-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $user->name }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $user->email }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- EDIT BUTTON: Changed color from amber to yellow (to match links/accents) --}}
                                        <a href="{{ route('admin.staff.edit', $user) }}"
                                           class="rounded-md p-1.5 text-yellow-600 hover:bg-yellow-50 hover:text-yellow-700">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path
                                                    d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </a>
            
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500 text-sm">
                                    No staff found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between border-t border-gray-100 px-4 py-3">
                <p class="hidden text-sm text-gray-500 sm:block">
                    Showing
                    <span class="font-semibold">{{ $users->firstItem() ?? 0 }}</span>
                    to
                    <span class="font-semibold">{{ $users->lastItem() ?? 0 }}</span>
                    of
                    <span class="font-semibold">{{ $users->total() }}</span>
                    staff
                </p>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>