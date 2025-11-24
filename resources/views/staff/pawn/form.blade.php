<x-staff-layout title="New Pawn Item">

<div class="max-w-3xl mx-auto py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Record Pawn Item</h1>
        <a href="{{ route('staff.pawn.index') }}"
           class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-red-50 border border-red-300 text-red-600 rounded mb-6">
            <p class="font-semibold mb-1">Please correct the following:</p>
            <ul class="text-sm list-disc ml-4">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('staff.pawn.store') }}" method="POST" enctype="multipart/form-data"
          class="space-y-6 bg-white rounded-xl shadow p-6">
        @csrf

        {{-- CUSTOMER --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
            <select name="customer_id"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                <option value="">Select Customer</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>
                @endforeach
            </select>
        </div>

        {{-- TITLE --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Item Title</label>
            <input type="text" name="title" required
                   class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        {{-- DESCRIPTION --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Optional..."></textarea>
        </div>

        {{-- PRICE --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pawn Price (Principal)</label>
            <input type="number" name="price" step="0.01" required
                   class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        {{-- INTEREST --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Interest Cost</label>
            <input type="number" name="interest_cost" step="0.01" required
                   class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="Example: 300">
        </div>

        @php
        $defaultDueDate = now()->addMonths(3)->format('Y-m-d');
        @endphp
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Due Date (Optional — defaults to 3 months)
            </label>

            <input type="date"
                name="due_date"
                value="{{ old('due_date', $defaultDueDate) }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        {{-- IMAGES --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Item Pictures</label>
            <input type="file" name="images[]" multiple accept="image/*"
                   class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <button type="submit"
                class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
            Save Pawn Item
        </button>

    </form>
</div>

</x-staff-layout>
