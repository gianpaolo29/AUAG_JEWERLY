<x-admin-layout title="Dashboard">
    <div class="space-y-8">
        {{-- Welcome & Quick Actions Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-gray-900 tracking-tight">Dashboard Overview</h1>
                <p class="text-gray-600 mt-1 font-serif">{{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>

        {{-- Key Metrics Grid --}}
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            {{-- Today's Revenue --}}
            <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Revenue</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">₱{{ number_format($todayRevenue, 2) }}</p>
                        <div class="mt-3 flex items-center gap-2">
                            @php
                                $dailyChange = $yesterdayRevenue > 0
                                    ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100
                                    : 0;
                            @endphp
                            <span class="inline-flex items-center text-xs font-medium {{ $dailyChange >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="{{ $dailyChange >= 0
                                                ? 'M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z'
                                                : 'M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z' }}"
                                          clip-rule="evenodd" />
                                </svg>
                                {{ number_format(abs($dailyChange), 1) }}%
                            </span>
                            <span class="text-xs text-gray-500">vs yesterday</span>
                        </div>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-indigo-100 to-indigo-50 rounded-xl text-indigo-600 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Monthly Revenue --}}
            <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">This Month</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">₱{{ number_format($monthlyRevenue, 2) }}</p>

                        @php
                            // From controller: $monthlyChange = (($monthlyRevenue - $lastMonthRevenue) / max($lastMonthRevenue, 1)) * 100;
                            $monthlyChangeBar = max(min($monthlyChange, 100), 0); // clamp 0–100 for the bar
                        @endphp

                        <div class="mt-3">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>vs last month</span>
                                <span class="font-medium {{ $monthlyChange >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $monthlyChange >= 0 ? '+' : '' }}{{ number_format($monthlyChange, 1) }}%
                                </span>
                            </div>
                            <div class="mt-2 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full transition-all duration-500"
                                    style="width: {{ $monthlyChangeBar }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-gradient-to-br from-blue-100 to-blue-50 rounded-xl text-blue-600 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>


            {{-- Active Pawn Value --}}
            <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Pawn Value</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">₱{{ number_format($totalPawnValue, 2) }}</p>
                        <div class="mt-3 flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg bg-amber-100 text-amber-800 text-xs font-medium">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                Active Items
                            </span>
                            <span class="text-xs text-gray-500">
                                @if($activePawnCount)
                                    {{ $activePawnCount }} active
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-amber-100 to-amber-50 rounded-xl text-amber-600 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Total Customers --}}
            <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300 group">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Customers</p>
                        <p class="mt-3 text-3xl font-bold text-gray-900">{{ number_format($totalCustomers) }}</p>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-emerald-100 to-emerald-50 rounded-xl text-emerald-600 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Revenue Chart --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Revenue Overview</h3>
                        <p class="text-sm text-gray-600 mt-1">Last 30 days performance</p>
                    </div>
                    
                </div>
                <div class="h-72">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            {{-- Quick Stats Panel --}}
            <div class="space-y-6">
                {{-- Low Stock Alert --}}
                <div class="bg-gradient-to-br from-white to-red-50 border border-red-100 rounded-2xl p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-red-100 rounded-lg">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900">Low Stock Alert</h4>
                            </div>
                            <p class="text-3xl font-bold text-red-600 mt-4">{{ $lowStockProducts }}</p>
                            <p class="text-sm text-gray-600 mt-1">Products need attention</p>
                            
                            {{-- UPDATED: Changed from <button> to <a> and added route --}}
                            <a href="{{ route('admin.products.index') }}" class="block text-center mt-4 w-full py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors">
                                View Inventory
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Pawn Status Chart --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4">Pawn Status</h3>
                    <div class="h-48">
                        <canvas id="pawnStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Section --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Recent Transactions --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-gray-900">Recent Transactions</h3>
                    <div class="flex items-center gap-3">
                        <a href="#" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                            View All
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Transaction</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentTransactions as $t)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="font-medium text-gray-900">#{{ $t->id }}</div>
                                            <div class="text-xs text-gray-500 mt-1">{{ $t->created_at->format('M d, Y • H:i') }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $t->customer->name ?? 'Walk-in Customer' }}</div>
                                        <div class="text-xs text-gray-500">{{ $t->customer->contact_no ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            @if($t->type === 'Buy') bg-emerald-100 text-emerald-800
                                            @elseif($t->type === 'Pawn') bg-amber-100 text-amber-800
                                            @else bg-blue-100 text-blue-800 @endif">
                                            {{ $t->type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">
                                            ₱{{ number_format($t->items->sum('line_total'), 2) }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $t->items->count() }} items</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            <p>No transactions yet</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('admin.pawn.create') }}" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors group">
                            <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900">New Pawn Transaction</span>
                        </a>
                        <a href="{{ route('admin.customers.create') }}" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors group">
                            <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900">Add New Customer</span>
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:bg-gray-50 transition-colors group">
                            <div class="p-2 bg-amber-100 rounded-lg text-amber-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900">Manage Inventory</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        Chart.defaults.font.family = "'Inter', 'system-ui', sans-serif";
        Chart.defaults.color = '#6b7280';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(17, 24, 39, 0.9)';
        Chart.defaults.plugins.tooltip.cornerRadius = 8;

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 300);
        revenueGradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
        revenueGradient.addColorStop(1, 'rgba(79, 70, 229, 0.02)');

        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($revenueChartLabels),
                datasets: [{
                    label: 'Revenue',
                    data: @json($revenueChartData),
                    fill: true,
                    backgroundColor: revenueGradient,
                    borderColor: '#4f46e5',
                    borderWidth: 3,
                    tension: 0.4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#4f46e5',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        padding: 12,
                        titleFont: { size: 13 },
                        bodyFont: { size: 14 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            borderDash: [3, 3]
                        },
                        border: { display: false },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'nearest'
                }
            }
        });

        // Pawn Status Chart
        const pawnCtx = document.getElementById('pawnStatusChart').getContext('2d');
        new Chart(pawnCtx, {
            type: 'doughnut',
            data: {
                labels: @json($pawnStatusLabels),
                datasets: [{
                    data: @json($pawnStatusData),
                    backgroundColor: [
                        '#4f46e5', // Active
                        '#0ea5e9', // Redeemed
                        '#f59e0b', // Forfeited / For sale
                        '#ef4444'  // Expired
                    ],
                    borderWidth: 0,
                    hoverOffset: 15,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    </script>

    <style>
        * {
            scroll-behavior: smooth;
        }
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1),
                        0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</x-admin-layout>
