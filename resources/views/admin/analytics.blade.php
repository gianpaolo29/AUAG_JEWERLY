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
                // Removed $views and $orders definitions as AI Suggestions is gone.
            @endphp
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Showing data for: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $rangeLabel }}</span>
                    </p>
                    <p class="text-xs text-gray-400">
                        Date range filters apply to most charts.
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

         
            {{-- TOP METRICS - NEW LAYOUT (3+2 GRID) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">

                {{-- Group 1: Time-Based Revenue (3 Columns) --}}
                <div class="md:col-span-2 lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @foreach ([
                        ['title' => 'Today Revenue', 'value' => $todayRevenue ?? 0, 'change' => $todayRevenueChange ?? 0, 'data' => $todayRevenueData ?? []],
                        ['title' => 'This Week Revenue', 'value' => $weekRevenue ?? 0, 'change' => $weekRevenueChange ?? 0, 'data' => $weekRevenueData ?? []],
                        ['title' => 'This Month Revenue', 'value' => $monthRevenue ?? 0, 'change' => $monthRevenueChange ?? 0, 'data' => $monthRevenueData ?? []],
                    ] as $metric)
                        @php
                            $isPositive = $metric['change'] >= 0;
                            $changeColor = $isPositive ? 'text-emerald-500' : 'text-red-500';
                            $changeIcon = $isPositive ? '↑' : '↓';
                        @endphp
                        <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-shadow dark:bg-gray-800 dark:border-gray-700 relative">
                            <p class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">{{ $metric['title'] }}</p>
                            <div class="flex items-end justify-between">
                                <p class="text-xl lg:text-2xl font-bold text-gray-900 dark:text-white">
                                    ₱{{ number_format($metric['value'], 2) }}
                                </p>
                                <p class="text-xs font-semibold {{ $changeColor }} flex items-center gap-1">
                                    <span class="text-sm">{{ $changeIcon }}</span>
                                    {{ abs($metric['change']) }}%
                                </p>
                            </div>
                            <div class="h-10 w-full mt-2">
                                <canvas id="mini-chart-{{ $loop->index }}" data-data='@json($metric['data'])' data-label="Revenue"></canvas>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Group 2: Cumulative/Average Metrics (2 Columns) --}}
                <div class="md:col-span-2 lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Total Revenue --}}
                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-md bg-indigo-50 dark:bg-indigo-900/30 dark:border-indigo-800">
                        <p class="mb-1 text-sm font-medium text-indigo-700 dark:text-indigo-300">Total Revenue (All Time)</p>
                        <p class="text-2xl font-extrabold text-indigo-800 dark:text-indigo-100">
                            ₱{{ number_format($totalRevenue ?? 0, 2) }}
                        </p>
                        <p class="text-xs mt-1 text-indigo-500 dark:text-indigo-400">Total earnings since inception.</p>
                    </div>

                    {{-- Total Orders / AOV --}}
                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-md dark:bg-gray-800 dark:border-gray-700">
                        <p class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders ({{ $rangeLabel }})</p>
                        <div class="flex items-end justify-between">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $totalOrders ?? 'N/A' }}
                            </p>
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                AOV: ₱{{ number_format($avgOrderValue ?? 0, 2) }}
                            </p>
                        </div>
                        <p class="text-xs font-semibold {{ ($totalOrdersChange ?? 0) >= 0 ? 'text-emerald-500' : 'text-red-500' }} mt-1">
                            {{ ($totalOrdersChange ?? 0) >= 0 ? '↑' : '↓' }} {{ abs($totalOrdersChange ?? 0) }}% vs prior period
                        </p>
                    </div>
                </div>
            </div>

            

            {{-- CONVERSION + PRODUCT SUMMARY & KEY STATS (ADJUSTED TO 3 COLUMNS) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column: Product & Customer Summary (Now taking up the entire left column space) --}}
                <div class="lg:col-span-1 space-y-6">
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
                                <dt class="text-sm text-gray-500 dark:text-gray-400">New This Month</dt>
                                <dd class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ $newCustomersThisMonth ?? 0 }}</dd>
                            </div>
                        </dl>
                    </div>
                    
                    {{-- Space filler for removed AI Suggestions --}}
                

                </div>

                {{-- Right Column: Conversion Funnel --}}
                <div class="lg:col-span-2">
                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Conversion Funnel ({{ $rangeLabel }})
                        </h3>
                        <div class="h-64">
                            <canvas id="conversionFunnelChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        

            {{-- CORE GRAPHS (TRENDS & CATEGORIES) --}}
            <section class="space-y-6">
                {{-- Sales trend (filtered range) --}}
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                        Sales Trend ({{ $rangeLabel }})
                    </h3>
                    <div class="h-80">
                        <canvas id="salesByDayChart"></canvas>
                    </div>
                </div>

                {{-- Product-related charts: Material + Revenue by Category --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Material Breakdown --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Product Material Breakdown (All)
                        </h3>
                        <div class="h-80">
                            <canvas id="materialChart"></canvas>
                        </div>
                    </div>

                    {{-- Revenue by Category --}}
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

    

            {{-- SECONDARY GRAPHS (TOP ITEMS, SERVICE STATUS, STAFF) --}}
            <section class="space-y-6">

                {{-- Top Products (Chart) & Staff Performance (2 Column) --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Top Products by Revenue chart (Horizontal Bar) --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Top Products by Revenue ({{ $rangeLabel }})
                        </h3>
                        <div class="h-80">
                            <canvas id="topProductsChart"></canvas>
                        </div>
                    </div>

                    {{-- Staff Performance --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Staff Performance (Sales, {{ $rangeLabel }})
                        </h3>
                        <div class="h-80">
                            <canvas id="staffSalesChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Pawn, Repair, Favorites, Views (2x2 Grid) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Pawn Status Chart --}}
                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                            Pawn Status ({{ $rangeLabel }})
                        </h3>
                        <div class="h-48">
                            <canvas id="pawnStatusChart"></canvas>
                        </div>
                    </div>

                    {{-- Repair Status Chart --}}
                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                            Repair Status ({{ $rangeLabel }})
                        </h3>
                        <div class="h-48">
                            <canvas id="repairStatusChart"></canvas>
                        </div>
                    </div>

                    {{-- Most Favorited Chart --}}
                    <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">
                            Most Favorited ({{ $rangeLabel }})
                        </h3>
                        <div class="h-48">
                            <canvas id="mostFavoritedChart"></canvas>
                        </div>
                    </div>

                    {{-- Most Viewed Chart --}}
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

            
            {{-- QUICK ACTIONS --}}
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Quick Actions --}}
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
        // Default chart colors for consistency
        const primaryColor = 'rgb(79, 70, 229)'; // Indigo-600
        const secondaryColor = 'rgb(16, 185, 129)'; // Emerald-500
        const alertColor = 'rgb(239, 68, 68)'; // Red-500

        // Function to destroy and re-render charts to handle dynamic data (e.g., filter change)
        function renderChart(ctx, type, data, options) {
            if (window.analyticsCharts && window.analyticsCharts[ctx.id]) {
                window.analyticsCharts[ctx.id].destroy();
            }
            if (!window.analyticsCharts) {
                window.analyticsCharts = {};
            }
            window.analyticsCharts[ctx.id] = new Chart(ctx, { type, data, options });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Helper for mini charts (if you add the sparkline data)
            document.querySelectorAll('[id^="mini-chart-"]').forEach(ctx => {
                const data = JSON.parse(ctx.getAttribute('data-data') || '[]');
                const label = ctx.getAttribute('data-label');
                if (data.length > 0) {
                    renderChart(ctx, 'line', {
                        labels: data.map((_, i) => i), // Placeholder labels
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

            // Conversion Funnel 
            const funnelCtx = document.getElementById('conversionFunnelChart');
            if (funnelCtx) {
                // We keep the logic to display the funnel data, colored normally, 
                // assuming the underlying data issue would be fixed by the user.
                const data = @json($funnelData);
                const colors = [primaryColor, primaryColor, primaryColor]; 

                renderChart(funnelCtx, 'bar', {
                    labels: @json($funnelLabels),
                    datasets: [{
                        label: 'Count',
                        data: data,
                        borderWidth: 1,
                        backgroundColor: colors,
                    }]
                }, {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { ticks: { precision: 0 } },
                        x: { beginAtZero: true }
                    }
                });
            }

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
                        y: {
                            ticks: {
                                callback: value => '₱' + value.toLocaleString()
                            }
                        }
                    }
                });
            }

            // Material
            const materialCtx = document.getElementById('materialChart');
            if (materialCtx) {
                renderChart(materialCtx, 'doughnut', {
                    labels: @json($materialLabels),
                    datasets: [{
                        data: @json($materialData),
                        borderWidth: 1,
                    }]
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
                    indexAxis: 'y', // Horizontal Bar Chart
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
                });
            }

            // Top Products by Revenue
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
                    indexAxis: 'y', // Horizontal Bar Chart
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
                });
            }

            // Pawn Status
            const pawnCtx = document.getElementById('pawnStatusChart');
            if (pawnCtx) {
                renderChart(pawnCtx, 'doughnut', {
                    labels: @json($pawnStatusLabels),
                    datasets: [{
                        data: @json($pawnStatusData),
                        borderWidth: 1,
                    }]
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
                    datasets: [{
                        data: @json($repairStatusData),
                        borderWidth: 1,
                    }]
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
                        y: {
                            ticks: {
                                callback: value => '₱' + value.toLocaleString()
                            }
                        }
                    }
                });
            }

            // Most Favorited chart (Horizontal Bar)
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

            // Most Viewed chart (Horizontal Bar)
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
        });
    </script>
</x-admin-layout>