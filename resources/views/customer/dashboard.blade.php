<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Admin Analytics') }}
        </h2>

        {{-- Flowbite + Chart.js (only if not already in your layout) --}}
        <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.5.1/flowbite.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- TOP STATS CARDS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Total Revenue --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                                Total Revenue
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                ₱{{ number_format($totalRevenue, 2) }}
                            </p>
                        </div>
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg dark:bg-green-900">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-10v2m0 8v2m8-10h2M4 8H2m18 4h2M4 12H2m18 4h2M4 16H2" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Today Revenue --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                                Today Revenue
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                ₱{{ number_format($todayRevenue, 2) }}
                            </p>
                        </div>
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg dark:bg-blue-900">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- This Month Revenue --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                                This Month Revenue
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                ₱{{ number_format($monthRevenue, 2) }}
                            </p>
                        </div>
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg dark:bg-purple-900">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 3h18v4H3V3zm0 8h18v10H3V11zm5 3v4m4-4v4m4-4v4" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Average Order Value --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                                Avg. Order Value
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                ₱{{ number_format($avgOrderValue, 2) }}
                            </p>
                        </div>
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-amber-100 rounded-lg dark:bg-amber-900">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 10h18M7 15h10m-9 4h8M6 6h12l1-2H5l1 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECOND ROW: PRODUCT / CUSTOMER STATS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Products Summary --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Products</h3>
                    <dl class="grid grid-cols-1 gap-3">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Products</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalProducts }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Published</dt>
                            <dd class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">{{ $publishedProducts }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Low Stock (&lt; 5)</dt>
                            <dd class="text-lg font-semibold text-red-600 dark:text-red-400">{{ $lowStockCount }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Customers Summary --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Customers</h3>
                    <dl class="grid grid-cols-1 gap-3">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Customers</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalCustomers }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">New This Month</dt>
                            <dd class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ $newCustomersThisMonth }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Top Products (table) --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Top Selling Products</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-3 py-2">Product</th>
                                    <th scope="col" class="px-3 py-2 text-right">Qty Sold</th>
                                    <th scope="col" class="px-3 py-2 text-right">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $p)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $p->name }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            {{ $p->total_qty }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            ₱{{ number_format($p->total_sales, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No sales data yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- CHARTS ROW --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Sales last 7 days --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Sales (Last 7 Days)</h3>
                    </div>
                    <div class="h-64">
                        <canvas id="salesByDayChart"></canvas>
                    </div>
                </div>

                {{-- Material Breakdown --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Material Breakdown</h3>
                    </div>
                    <div class="h-64">
                        <canvas id="materialChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Flowbite JS (if not already included in layout) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.5.1/flowbite.min.js"></script>

    {{-- Charts init --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sales by day
            const salesCtx = document.getElementById('salesByDayChart');
            if (salesCtx) {
                new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels: @json($salesByDayLabels),
                        datasets: [{
                            label: 'Sales (₱)',
                            data: @json($salesByDayData),
                            tension: 0.4,
                            fill: false,
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                ticks: {
                                    callback: value => '₱' + value.toLocaleString()
                                }
                            }
                        }
                    }
                });
            }

            // Material breakdown
            const materialCtx = document.getElementById('materialChart');
            if (materialCtx) {
                new Chart(materialCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($materialLabels),
                        datasets: [{
                            data: @json($materialData),
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-admin-layout>
