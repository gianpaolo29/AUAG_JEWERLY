<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Admin Analytics') }} 📊
        </h2>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.5.1/flowbite.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </x-slot>

    <div class="space-y-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- DATE RANGE FILTERS --}}
            @php
                $currentRange = $range ?? request('range', '30d');
            @endphp

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Showing data for: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $rangeLabel }}</span>
                    </p>
                    <p class="text-xs text-gray-400">
                        Date range filters apply to charts and cards.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.analytics', ['range' => 'today']) }}"
                       class="px-3 py-1 text-xs font-medium rounded-full border transition-colors
                             {{ $currentRange === 'today'
                                 ? 'bg-indigo-600 text-white border-indigo-600'
                                 : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700' }}">
                        Today
                    </a>
                    <a href="{{ route('admin.analytics', ['range' => '7d']) }}"
                       class="px-3 py-1 text-xs font-medium rounded-full border transition-colors
                             {{ $currentRange === '7d'
                                 ? 'bg-indigo-600 text-white border-indigo-600'
                                 : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700' }}">
                        Last 7 Days
                    </a>
                    <a href="{{ route('admin.analytics', ['range' => '30d']) }}"
                       class="px-3 py-1 text-xs font-medium rounded-full border transition-colors
                             {{ $currentRange === '30d'
                                 ? 'bg-indigo-600 text-white border-indigo-600'
                                 : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700' }}">
                        Last 30 Days
                    </a>
                    <a href="{{ route('admin.analytics', ['range' => 'all']) }}"
                       class="px-3 py-1 text-xs font-medium rounded-full border transition-colors
                             {{ $currentRange === 'all'
                                 ? 'bg-indigo-600 text-white border-indigo-600'
                                 : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700' }}">
                        All Time
                    </a>
                </div>
            </div>

            {{-- TOP METRICS (ALL FILTERED BY RANGE) - 3 + 2 GRID --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">

                {{-- Group 1: Range Metrics with Sparklines (3 Columns) --}}
                <div class="md:col-span-2 lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @foreach ([
                        ['title' => "Revenue ({$rangeLabel})", 'type' => 'money', 'value' => $rangeRevenue ?? 0, 'change' => $rangeRevenueChange ?? 0, 'data' => $rangeRevenueData ?? [], 'label' => 'Revenue'],
                        ['title' => "Orders ({$rangeLabel})",  'type' => 'count', 'value' => $rangeOrders ?? 0,  'change' => $rangeOrdersChange ?? 0,  'data' => $rangeOrdersData ?? [],  'label' => 'Orders'],
                        ['title' => "AOV ({$rangeLabel})",     'type' => 'money', 'value' => $rangeAov ?? 0,     'change' => $rangeAovChange ?? 0,     'data' => $rangeAovData ?? [],     'label' => 'AOV'],
                    ] as $metric)
                        @php
                            $isPositive = ($metric['change'] ?? 0) >= 0;
                            $changeColor = $isPositive ? 'text-emerald-500' : 'text-red-500';
                            $changeIcon  = $isPositive ? '↑' : '↓';
                        @endphp

                        <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-shadow dark:bg-gray-800 dark:border-gray-700 relative">
                            <p class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">{{ $metric['title'] }}</p>

                            <div class="flex items-end justify-between">
                                <p class="text-xl lg:text-2xl font-bold text-gray-900 dark:text-white">
                                    @if($metric['type'] === 'money')
                                        ₱{{ number_format($metric['value'], 2) }}
                                    @else
                                        {{ number_format($metric['value']) }}
                                    @endif
                                </p>

                                <p class="text-xs font-semibold {{ $changeColor }} flex items-center gap-1">
                                    <span class="text-sm">{{ $changeIcon }}</span>
                                    {{ abs($metric['change']) }}%
                                </p>
                            </div>

                            <div class="h-10 w-full mt-2">
                                <canvas
                                    id="mini-chart-{{ $loop->index }}"
                                    data-data='@json($metric['data'])'
                                    data-label="{{ $metric['label'] }}"
                                ></canvas>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Group 2: Other Range Cards (2 Columns) --}}
                <div class="md:col-span-2 lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">

                    {{-- Items Sold --}}
                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <p class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">Items Sold ({{ $rangeLabel }})</p>
                        <div class="flex items-end justify-between">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($itemsSold ?? 0) }}
                            </p>
                            <p class="text-xs font-semibold {{ ($itemsSoldChange ?? 0) >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                {{ ($itemsSoldChange ?? 0) >= 0 ? '↑' : '↓' }} {{ abs($itemsSoldChange ?? 0) }}%
                            </p>
                        </div>
                        <p class="text-xs mt-1 text-gray-500 dark:text-gray-400">Total quantity sold in selected range.</p>
                    </div>

                    {{-- Unique Buyers --}}
                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-md bg-indigo-50 dark:bg-indigo-900/30 dark:border-indigo-800">
                        <p class="mb-1 text-sm font-medium text-indigo-700 dark:text-indigo-300">Customer ({{ $rangeLabel }})</p>
                        <div class="flex items-end justify-between">
                            <p class="text-2xl font-extrabold text-indigo-800 dark:text-indigo-100">
                                {{ number_format($uniqueBuyers ?? 0) }}
                            </p>
                            <p class="text-xs font-semibold {{ ($uniqueBuyersChange ?? 0) >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                {{ ($uniqueBuyersChange ?? 0) >= 0 ? '↑' : '↓' }} {{ abs($uniqueBuyersChange ?? 0) }}%
                            </p>
                        </div>
                        <p class="text-xs mt-1 text-indigo-500 dark:text-indigo-400">Distinct customers who purchased.</p>
                    </div>

                </div>
            </div>

            {{-- PRODUCT + CUSTOMER SUMMARY (NO FUNNEL) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Products summary --}}
                <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-indigo-500">📦</span> Product Status
                    </h3>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3">
                        <div class="col-span-2 flex items-center justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Total Products</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalProducts ?? 0 }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Published</dt>
                            <dd class="text-lg font-semibold text-emerald-600 dark:text-emerald-400">{{ $publishedProducts ?? 0 }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Low Stock (&lt; 5)</dt>
                            <dd class="text-lg font-semibold text-red-600 dark:text-red-400">{{ $lowStockCount ?? 0 }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Customers summary --}}
                <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-blue-500">👥</span> Customer Stats
                    </h3>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3">
                        <div class="col-span-2 flex items-center justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Total Customers</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalCustomers ?? 0 }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">New ({{ $rangeLabel }})</dt>
                            <dd class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ $newCustomersInRange ?? 0 }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- CORE GRAPHS --}}
            <section class="space-y-6">
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                        Sales Trend ({{ $rangeLabel }})
                    </h3>
                    <div class="h-80">
                        <canvas id="salesByDayChart"></canvas>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Product Material Breakdown (All)
                        </h3>
                        <div class="h-80">
                            <canvas id="materialChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Revenue by Category ({{ $rangeLabel }})
                        </h3>
                        <div class="h-80">
                            <canvas id="revenueByCategoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>

            {{-- SECONDARY GRAPHS --}}
            <section class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Top Products by Revenue ({{ $rangeLabel }})
                        </h3>
                        <div class="h-80">
                            <canvas id="topProductsChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Staff Performance (Sales, {{ $rangeLabel }})
                        </h3>
                        <div class="h-80">
                            <canvas id="staffSalesChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                            Pawn Status ({{ $rangeLabel }})
                        </h3>
                        <div class="h-48">
                            <canvas id="pawnStatusChart"></canvas>
                        </div>
                    </div>

                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                            Repair Status ({{ $rangeLabel }})
                        </h3>
                        <div class="h-48">
                            <canvas id="repairStatusChart"></canvas>
                        </div>
                    </div>

                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                            Most Favorited ({{ $rangeLabel }})
                        </h3>
                        <div class="h-48">
                            <canvas id="mostFavoritedChart"></canvas>
                        </div>
                    </div>

                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                            Most Viewed ({{ $rangeLabel }})
                        </h3>
                        <div class="h-48">
                            <canvas id="mostViewedChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>

            <section class="space-y-6 mt-6">
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                        Frequently Bought Together (All Time)
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        (Minimum support: {{ $minSupport ?? 5 }} transactions).
                    </p>
                    <div class="h-80">
                        <canvas id="frequentCombosChart"></canvas>
                    </div>
                </div>
            </section>

            {{-- QUICK ACTIONS --}}
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="w-full p-5 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700 lg:col-span-2">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                        🚀 Quick Actions
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($quickActions as $action)
                            <a href="{{ $action['href'] }}"
                               class="flex items-center justify-between w-full p-3 text-sm bg-gray-50 rounded-lg hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-700 border border-gray-100 dark:border-gray-700 transition-colors">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $action['label'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $action['description'] }}
                                    </p>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
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
        const primaryColor = 'rgb(79, 70, 229)';
        const secondaryColor = 'rgb(16, 185, 129)';

        function renderChart(ctx, type, data, options) {
            if (window.analyticsCharts && window.analyticsCharts[ctx.id]) {
                window.analyticsCharts[ctx.id].destroy();
            }
            if (!window.analyticsCharts) window.analyticsCharts = {};
            window.analyticsCharts[ctx.id] = new Chart(ctx, { type, data, options });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Mini charts
            document.querySelectorAll('[id^="mini-chart-"]').forEach(ctx => {
                const data = JSON.parse(ctx.getAttribute('data-data') || '[]');
                const label = ctx.getAttribute('data-label');
                if (data.length > 0) {
                    renderChart(ctx, 'line', {
                        labels: data.map((_, i) => i),
                        datasets: [{
                            label: label,
                            data: data,
                            borderColor: primaryColor,
                            borderWidth: 2,
                            pointRadius: 0,
                            fill: true,
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            tension: 0.4,
                        }]
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: false } },
                        scales: { x: { display: false }, y: { display: false } }
                    });
                }
            });

            // Sales by Day
            const salesCtx = document.getElementById('salesByDayChart');
            if (salesCtx) {
                renderChart(salesCtx, 'line', {
                    labels: @json($salesByDayLabels),
                    datasets: [{
                        label: 'Sales (₱)',
                        data: @json($salesByDayData),
                        borderColor: primaryColor,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: false,
                    }]
                }, {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { ticks: { callback: value => '₱' + value.toLocaleString() } }
                    }
                });
            }

            // Material
            const materialCtx = document.getElementById('materialChart');
            if (materialCtx) {
                renderChart(materialCtx, 'doughnut', {
                    labels: @json($materialLabels),
                    datasets: [{ data: @json($materialData), borderWidth: 1 }]
                }, {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                });
            }

            // Revenue by Category
            const catCtx = document.getElementById('revenueByCategoryChart');
            if (catCtx) {
                renderChart(catCtx, 'bar', {
                    labels: @json($revenueByCategoryLabels),
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: @json($revenueByCategoryData),
                        borderWidth: 1,
                        backgroundColor: secondaryColor,
                    }]
                }, {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { callback: value => '₱' + value.toLocaleString() } }
                    }
                });
            }

            // Top Products
            const topProdCtx = document.getElementById('topProductsChart');
            if (topProdCtx) {
                renderChart(topProdCtx, 'bar', {
                    labels: @json($topProducts->pluck('name')),
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: @json($topProducts->pluck('total_sales')),
                        borderWidth: 1,
                        backgroundColor: primaryColor,
                    }]
                }, {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { callback: value => '₱' + value.toLocaleString() } }
                    }
                });
            }

            // Pawn Status
            const pawnCtx = document.getElementById('pawnStatusChart');
            if (pawnCtx) {
                renderChart(pawnCtx, 'doughnut', {
                    labels: @json($pawnStatusLabels),
                    datasets: [{ data: @json($pawnStatusData), borderWidth: 1 }]
                }, {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                });
            }

            // Repair Status
            const repairCtx = document.getElementById('repairStatusChart');
            if (repairCtx) {
                renderChart(repairCtx, 'doughnut', {
                    labels: @json($repairStatusLabels),
                    datasets: [{ data: @json($repairStatusData), borderWidth: 1 }]
                }, {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                });
            }

            // Staff Sales
            const staffCtx = document.getElementById('staffSalesChart');
            if (staffCtx) {
                renderChart(staffCtx, 'bar', {
                    labels: @json($staffSalesLabels),
                    datasets: [{
                        label: 'Sales (₱)',
                        data: @json($staffSalesData),
                        borderWidth: 1,
                        backgroundColor: primaryColor,
                    }]
                }, {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { ticks: { callback: value => '₱' + value.toLocaleString() } }
                    }
                });
            }

            // Most Favorited
            const favCtx = document.getElementById('mostFavoritedChart');
            if (favCtx) {
                renderChart(favCtx, 'bar', {
                    labels: @json($mostFavorited->pluck('name')),
                    datasets: [{
                        label: 'Favorites',
                        data: @json($mostFavorited->pluck('total_favorites')),
                        borderWidth: 1,
                        backgroundColor: primaryColor,
                    }]
                }, {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { ticks: { precision: 0 } } }
                });
            }

            // Most Viewed
            const viewedCtx = document.getElementById('mostViewedChart');
            if (viewedCtx) {
                renderChart(viewedCtx, 'bar', {
                    labels: @json($mostViewed->pluck('name')),
                    datasets: [{
                        label: 'Views',
                        data: @json($mostViewed->pluck('views')),
                        borderWidth: 1,
                        backgroundColor: secondaryColor,
                    }]
                }, {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { ticks: { precision: 0 } } }
                });
            }

            // Frequent Combos
            const combosCtx = document.getElementById('frequentCombosChart');
            if (combosCtx) {
                const comboLabels = @json($frequentComboLabels ?? []);
                const comboData   = @json($frequentComboSupport ?? []);

                if (comboLabels.length > 0) {
                    renderChart(combosCtx, 'bar', {
                        labels: comboLabels,
                        datasets: [{
                            label: 'Number of Buy Transactions',
                            data: comboData,
                            borderWidth: 1,
                            backgroundColor: primaryColor,
                        }]
                    }, {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        return ctx.raw + ' transactions';
                                    }
                                }
                            }
                        },
                        scales: { x: { ticks: { precision: 0 } } }
                    });
                }
            }
        });
    </script>
</x-admin-layout>
