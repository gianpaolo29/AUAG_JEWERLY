<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Admin Analytics') }}
        </h2>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.5.1/flowbite.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- DATE RANGE FILTERS --}}
            @php
                $currentRange = $range ?? request('range', '30d');
            @endphp
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-2">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Showing data for: <span class="font-semibold text-gray-900 dark:text-white">{{ $rangeLabel }}</span>
                    </p>
                    <p class="text-xs text-gray-400">
                        Filters apply to charts.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.analytics', ['range' => 'today']) }}"
                       class="px-3 py-1 text-xs font-medium rounded-full border
                              {{ $currentRange === 'today'
                                  ? 'bg-indigo-600 text-white border-indigo-600'
                                  : 'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600' }}">
                        Today
                    </a>
                    <a href="{{ route('admin.analytics', ['range' => '7d']) }}"
                       class="px-3 py-1 text-xs font-medium rounded-full border
                              {{ $currentRange === '7d'
                                  ? 'bg-indigo-600 text-white border-indigo-600'
                                  : 'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600' }}">
                        Last 7 Days
                    </a>
                    <a href="{{ route('admin.analytics', ['range' => '30d']) }}"
                       class="px-3 py-1 text-xs font-medium rounded-full border
                              {{ $currentRange === '30d'
                                  ? 'bg-indigo-600 text-white border-indigo-600'
                                  : 'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600' }}">
                        Last 30 Days
                    </a>
                    <a href="{{ route('admin.analytics', ['range' => 'all']) }}"
                       class="px-3 py-1 text-xs font-medium rounded-full border
                              {{ $currentRange === 'all'
                                  ? 'bg-indigo-600 text-white border-indigo-600'
                                  : 'bg-white text-gray-700 border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600' }}">
                        All Time
                    </a>
                </div>
            </div>

            {{-- TOP METRICS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                {{-- Total Revenue --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        ₱{{ number_format($totalRevenue, 2) }}
                    </p>
                </div>

                {{-- Today Revenue --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">Today Revenue</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        ₱{{ number_format($todayRevenue, 2) }}
                    </p>
                </div>

                {{-- This Week Revenue --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">This Week Revenue</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        ₱{{ number_format($weekRevenue, 2) }}
                    </p>
                </div>

                {{-- This Month Revenue --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">This Month Revenue</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        ₱{{ number_format($monthRevenue, 2) }}
                    </p>
                </div>

                {{-- AOV --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">Average Order Value</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        ₱{{ number_format($avgOrderValue, 2) }}
                    </p>
                </div>
            </div>

            {{-- PRODUCT & CUSTOMER SUMMARY + TOP PRODUCTS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Products summary --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Products</h3>
                    <dl class="grid grid-cols-1 gap-3">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Total Products</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalProducts }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Published</dt>
                            <dd class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">{{ $publishedProducts }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Low Stock (&lt; 5)</dt>
                            <dd class="text-lg font-semibold text-red-600 dark:text-red-400">{{ $lowStockCount }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Customers summary --}}
                <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Customers</h3>
                    <dl class="grid grid-cols-1 gap-3">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Total Customers</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalCustomers }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">New This Month</dt>
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
                                    <th class="px-3 py-2">Product</th>
                                    <th class="px-3 py-2 text-right">Qty</th>
                                    <th class="px-3 py-2 text-right">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $p)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $p->name }}
                                        </td>
                                        <td class="px-3 py-2 text-right">{{ $p->total_qty }}</td>
                                        <td class="px-3 py-2 text-right">
                                            ₱{{ number_format($p->total_sales, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No sales yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- BIG GRAPH SECTIONS --}}
            <section class="space-y-8">

                {{-- Revenue overview (Today / Week / Month / Total) --}}
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                        Revenue Overview (All Time)
                    </h3>
                    <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <div class="h-72">
                            <canvas id="revenueOverviewChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Conversion Funnel (Views → Favorites → Orders) --}}
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                        Conversion Funnel ({{ $rangeLabel }})
                    </h3>
                    <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <div class="h-72">
                            <canvas id="conversionFunnelChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Sales trend (filtered range) --}}
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                        Sales Trend ({{ $rangeLabel }})
                    </h3>
                    <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <div class="h-72">
                            <canvas id="salesByDayChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Product-related charts: Material + Revenue by Category --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Material Breakdown (All Products)
                        </h3>
                        <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm">
                            <div class="h-72">
                                <canvas id="materialChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Revenue by Category ({{ $rangeLabel }})
                        </h3>
                        <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm">
                            <div class="h-72">
                                <canvas id="revenueByCategoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Top Products by Revenue chart --}}
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                        Top Products by Revenue ({{ $rangeLabel }})
                    </h3>
                    <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <div class="h-72">
                            <canvas id="topProductsChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Pawn & Repair charts --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Pawn Items by Status ({{ $rangeLabel }})
                        </h3>
                        <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm">
                            <div class="h-72">
                                <canvas id="pawnStatusChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Repairs by Status ({{ $rangeLabel }})
                        </h3>
                        <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm">
                            <div class="h-72">
                                <canvas id="repairStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Staff Performance --}}
                <div>
                    <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                        Staff Performance (Sales, {{ $rangeLabel }})
                    </h3>
                    <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm">
                        <div class="h-72">
                            <canvas id="staffSalesChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Favorites & Most Viewed as tables + charts --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Most Favorited --}}
                    <div class="w-full p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                            Most Favorited Products ({{ $rangeLabel }})
                        </h3>

                        {{-- Chart --}}
                        <div class="h-48 mb-4">
                            <canvas id="mostFavoritedChart"></canvas>
                        </div>

                        {{-- Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th class="px-3 py-2">Product</th>
                                        <th class="px-3 py-2 text-right">Favorites</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($mostFavorited as $f)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">
                                                {{ $f->name }}
                                            </td>
                                            <td class="px-3 py-2 text-right">{{ $f->total_favorites }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                                No favorites yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Most Viewed --}}
                    <div class="w-full p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                            Most Viewed Products ({{ $rangeLabel }})
                        </h3>

                        {{-- Chart --}}
                        <div class="h-48 mb-4">
                            <canvas id="mostViewedChart"></canvas>
                        </div>

                        {{-- Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th class="px-3 py-2">Product</th>
                                        <th class="px-3 py-2 text-right">Views</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($mostViewed as $v)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">
                                                {{ $v->name }}
                                            </td>
                                            <td class="px-3 py-2 text-right">{{ $v->views }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                                No views logged yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            {{-- AI SUGGESTIONS + QUICK ACTIONS --}}
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- AI Suggestions --}}
                <div class="w-full p-5 bg-white border border-indigo-100 rounded-xl shadow-sm dark:bg-gray-900 dark:border-indigo-900/40">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">
                        AI Suggestions
                    </h3>
                    <ul class="space-y-3">
                        @foreach($aiSuggestions as $s)
                            <li class="flex items-start gap-2">
                                <span class="mt-1 inline-flex w-2 h-2 rounded-full bg-indigo-500"></span>
                                <p class="text-sm text-gray-700 dark:text-gray-200">{!! $s !!}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Quick Actions --}}
                <div class="w-full p-5 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">
                        Quick Actions
                    </h3>
                    <div class="space-y-3">
                        @foreach($quickActions as $action)
                            <a href="{{ $action['href'] }}"
                               class="flex items-center justify-between w-full px-3 py-3 text-sm bg-gray-50 rounded-lg hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-700 border border-gray-100 dark:border-gray-700">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $action['label'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $action['description'] }}
                                    </p>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.5.1/flowbite.min.js"></script>

    {{-- Chart.js init --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Revenue overview (all time cards)
            const revOverviewCtx = document.getElementById('revenueOverviewChart');
            if (revOverviewCtx) {
                new Chart(revOverviewCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Today', 'This Week', 'This Month', 'Total'],
                        datasets: [{
                            label: 'Revenue (₱)',
                            data: [
                                {{ $todayRevenue }},
                                {{ $weekRevenue }},
                                {{ $monthRevenue }},
                                {{ $totalRevenue }},
                            ],
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
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

            // Conversion Funnel
            const funnelCtx = document.getElementById('conversionFunnelChart');
            if (funnelCtx) {
                new Chart(funnelCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($funnelLabels),
                        datasets: [{
                            label: 'Count',
                            data: @json($funnelData),
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            // Sales by Day
            const salesCtx = document.getElementById('salesByDayChart');
            if (salesCtx) {
                new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels: @json($salesByDayLabels),
                        datasets: [{
                            label: 'Sales (₱)',
                            data: @json($salesByDayData),
                            borderWidth: 2,
                            tension: 0.4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
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

            // Material
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
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }

            // Revenue by Category
            const catCtx = document.getElementById('revenueByCategoryChart');
            if (catCtx) {
                new Chart(catCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($revenueByCategoryLabels),
                        datasets: [{
                            label: 'Revenue (₱)',
                            data: @json($revenueByCategoryData),
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                ticks: {
                                    callback: value => '₱' + value.toLocaleString()
                                }
                            }
                        }
                    }
                });
            }

            // Top Products by Revenue
            const topProdCtx = document.getElementById('topProductsChart');
            if (topProdCtx) {
                new Chart(topProdCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($topProducts->pluck('name')),
                        datasets: [{
                            label: 'Revenue (₱)',
                            data: @json($topProducts->pluck('total_sales')),
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
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

            // Pawn Status
            const pawnCtx = document.getElementById('pawnStatusChart');
            if (pawnCtx) {
                new Chart(pawnCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($pawnStatusLabels),
                        datasets: [{
                            data: @json($pawnStatusData),
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }

            // Repair Status
            const repairCtx = document.getElementById('repairStatusChart');
            if (repairCtx) {
                new Chart(repairCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($repairStatusLabels),
                        datasets: [{
                            data: @json($repairStatusData),
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }

            // Staff Sales
            const staffCtx = document.getElementById('staffSalesChart');
            if (staffCtx) {
                new Chart(staffCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($staffSalesLabels),
                        datasets: [{
                            label: 'Sales (₱)',
                            data: @json($staffSalesData),
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
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

            // Most Favorited chart
            const favCtx = document.getElementById('mostFavoritedChart');
            if (favCtx) {
                new Chart(favCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($mostFavorited->pluck('name')),
                        datasets: [{
                            label: 'Favorites',
                            data: @json($mostFavorited->pluck('total_favorites')),
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            // Most Viewed chart
            const viewedCtx = document.getElementById('mostViewedChart');
            if (viewedCtx) {
                new Chart(viewedCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($mostViewed->pluck('name')),
                        datasets: [{
                            label: 'Views',
                            data: @json($mostViewed->pluck('views')),
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-admin-layout>
