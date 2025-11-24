<x-staff-layout title="Pawn Items">

<div class="max-w-5xl mx-auto py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">My Pawn Items</h1>

        <a href="{{ route('staff.pawn.create') }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            + New Pawn
        </a>
    </div>

    <div class="bg-white rounded-xl shadow p-6 divide-y">
        @forelse ($pawnItems as $p)
            <div class="py-4 flex items-center justify-between">
                <div>
                    <p class="font-semibold text-gray-900">{{ $p->title }}</p>
                    <p class="text-sm text-gray-500">
                        {{ $p->customer->name ?? 'Unknown' }} •
                        Principal: ₱{{ number_format($p->price, 2) }}
                    </p>
                </div>

                <span class="text-xs px-3 py-1.5 rounded-lg
                    {{ $p->status === 'active' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                    {{ ucfirst($p->status) }}
                </span>
            </div>
        @empty
            <p class="text-gray-500 text-sm py-4">No pawn items yet.</p>
        @endforelse

        <div class="mt-4">
            {{ $pawnItems->links() }}
        </div>

    </div>
</div>

</x-staff-layout>
