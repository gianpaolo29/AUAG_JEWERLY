<x-staff-layout title="Staff Dashboard">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- PAGE TITLE --}}
        <h1 class="text-3xl font-bold text-gray-800 mb-6">
            Staff Dashboard
        </h1>

        {{-- KPI CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- Pawn Items --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-yellow-500">
                <p class="text-sm text-gray-500">Pawn Items Handled</p>
                <p class="text-3xl font-bold">{{ $pawnHandled }}</p>
            </div>

            {{-- Repairs Completed --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                <p class="text-sm text-gray-500">Completed Repairs</p>
                <p class="text-3xl font-bold">{{ $completedRepairs }}</p>
            </div>

            {{-- Active Repairs --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                <p class="text-sm text-gray-500">Active Repairs</p>
                <p class="text-3xl font-bold">{{ $activeRepairs }}</p>
            </div>

            {{-- Transactions --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
                <p class="text-sm text-gray-500">Transactions Processed</p>
                <p class="text-3xl font-bold">{{ $transactionsCount }}</p>
            </div>

        </div>


        {{-- CHARTS + RECENT ITEMS --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">

            {{-- Recent Pawn Items --}}
            <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-2">
                <h2 class="text-xl font-semibold mb-4">Recent Pawn Items</h2>

                @forelse($recentPawnItems as $item)
                    <div class="flex justify-between items-center bg-gray-50 p-4 rounded-xl mb-3">
                        <div>
                            <p class="font-semibold">{{ $item->item_name }}</p>
                            <p class="text-gray-600 text-sm">
                                Customer: {{ $item->customer->name ?? 'Unknown' }}
                            </p>
                        </div>

                        <span class="px-3 py-1 text-xs rounded-full
                            @if($item->status === 'active') bg-yellow-100 text-yellow-700
                            @elseif($item->status === 'redeemed') bg-green-100 text-green-700
                            @elseif($item->status === 'forfeited') bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ ucfirst($item->status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500">No recent pawn items.</p>
                @endforelse
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-xl shadow-md p-6 space-y-4">
                <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>

                <a href=""
                    class="block p-4 bg-yellow-500 text-white rounded-xl text-center">
                    New Pawn Item
                </a>

                <a href=""
                    class="block p-4 bg-blue-500 text-white rounded-xl text-center">
                    View Repairs
                </a>

                <a href=""
                    class="block p-4 bg-green-500 text-white rounded-xl text-center">
                    View Transactions
                </a>

                <a href=""
                    class="block p-4 bg-gray-800 text-white rounded-xl text-center">
                    Staff Profile
                </a>
            </div>

        </div>


        {{-- Recent Activity Logs --}}
        <div class="bg-white rounded-xl shadow-md p-6 mt-8">
            <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>

            <ul class="space-y-3">
                @forelse($recentTransactions as $log)
                    <li class="flex items-start gap-3">
                        <span class="w-3 h-3 bg-blue-500 rounded-full mt-1"></span>
                        <p class="text-gray-700">
                            {{ ucfirst($log->type) }} transaction performed by 
                            <strong>{{ $log->staff->name ?? 'Unknown Staff' }}</strong>.
                            <span class="text-gray-500 text-sm">
                                {{ $log->created_at->diffForHumans() }}
                            </span>
                        </p>
                    </li>
                @empty
                    <p class="text-gray-500">No recent activity logged.</p>
                @endforelse
            </ul>

        </div>

    </div>

</x-staff-layout>
