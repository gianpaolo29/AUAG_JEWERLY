<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Admin Analytics') }} 📊
        </h2>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.5.1/flowbite.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </x-slot>

    <div class="space-y-4"> {{-- Reduced overall vertical space --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- 1. HEADER & DATE FILTERS (Compact) --}}
            @php
                $currentRange = $range ?? request('range', '30d');
            @endphp

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-2">
                {{-- LEFT: label --}}
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Showing data for:
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $rangeLabel }}</span>
                    </p>
                </div>

                {{-- RIGHT: controls --}}
                @php
                    $currentRange = $range ?? request('range', '30d');

                    $ranges = [
                        'today' => 'Today',
                        '7d'    => '7 Days',
                        '30d'   => '30 Days',
                        'all'   => 'All Time',
                    ];

                    $currentLabel = $ranges[$currentRange] ?? '30 Days';
                    $month = request('month', now()->format('Y-m'));
                @endphp

                <div class="flex flex-wrap items-center gap-2 md:justify-end">
                    {{-- Range dropdown --}}
                    <div class="relative">
                        <button
                            id="rangeDropdownButton"
                            data-dropdown-toggle="rangeDropdownMenu"
                            type="button"
                            class="inline-flex items-center justify-between gap-2 min-w-[120px] px-3 py-2
                       text-[10px] uppercase tracking-wider font-semibold rounded-md border transition-colors
                       bg-white text-gray-700 border-gray-200 hover:bg-gray-50
                       dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-700"
                        >
                            {{ $currentLabel }}
                            <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div
                            id="rangeDropdownMenu"
                            class="hidden z-10 mt-2 w-44 rounded-md border border-gray-200 bg-white shadow-sm
                       dark:bg-gray-800 dark:border-gray-700"
                        >
                            <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                                @foreach($ranges as $key => $label)
                                    <li>
                                        <a
                                            href="{{ route('admin.analytics', ['range' => $key, 'month' => $month]) }}"
                                            class="block px-4 py-2 text-[11px] uppercase tracking-wider transition-colors
                                       hover:bg-gray-50 dark:hover:bg-gray-700
                                       {{ $currentRange === $key ? 'bg-indigo-600 text-white dark:bg-indigo-600' : '' }}"
                                        >
                                            {{ $label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- Month + Download --}}
                    <form method="GET" class="flex flex-wrap items-center gap-2">
                        {{-- keep range when changing month --}}
                        <input type="hidden" name="range" value="{{ $currentRange }}">

                        <input
                            type="month"
                            name="month"
                            value="{{ $month }}"
                            class="px-3 py-2 text-sm rounded-md border border-gray-200 bg-white
                       dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200"
                        >

                        <a
                            href="{{ route('admin.reports.monthly-sales.download', ['month' => $month]) }}"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md bg-indigo-600 text-white
                       hover:bg-indigo-700 transition-colors"
                        >
                            Download Monthly PDF
                        </a>
                    </form>
                </div>
            </div>


            {{-- 2. METRICS GRID (Compact) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">

                {{-- Range Metrics (3 cols) --}}
                <div class="md:col-span-2 lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach ([
                        ['title' => "Revenue", 'type' => 'money', 'value' => $rangeRevenue ?? 0, 'change' => $rangeRevenueChange ?? 0, 'data' => $rangeRevenueData ?? [], 'label' => 'Revenue'],
                        ['title' => "Orders",  'type' => 'count', 'value' => $rangeOrders ?? 0,  'change' => $rangeOrdersChange ?? 0,  'data' => $rangeOrdersData ?? [],  'label' => 'Orders'],
                        ['title' => "AOV",     'type' => 'money', 'value' => $rangeAov ?? 0,     'change' => $rangeAovChange ?? 0,     'data' => $rangeAovData ?? [],     'label' => 'AOV'],
                    ] as $metric)
                        @php
                            $isPositive = ($metric['change'] ?? 0) >= 0;
                            $changeColor = $isPositive ? 'text-emerald-500' : 'text-rose-500';
                            $changeBg    = $isPositive ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-rose-50 dark:bg-rose-900/20';
                            $changeIcon  = $isPositive ? '↑' : '↓';
                        @endphp

                        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow dark:bg-gray-800 dark:border-gray-700 flex flex-col justify-between h-full">
                            <div>
                                <div class="flex justify-between items-start mb-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">{{ $metric['title'] }}</p>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $changeBg }} {{ $changeColor }}">
                                        {{ $changeIcon }}{{ abs($metric['change']) }}%
                                    </span>
                                </div>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">
                                    @if($metric['type'] === 'money')
                                        ₱{{ number_format($metric['value'], 2) }}
                                    @else
                                        {{ number_format($metric['value']) }}
                                    @endif
                                </p>
                            </div>

                            {{-- Sparkline (Reduced Height) --}}
                            <div class="h-8 w-full mt-2">
                                <canvas id="mini-chart-{{ $loop->index }}"
                                        data-data='@json($metric['data'])'
                                        data-label="{{ $metric['label'] }}"
                                        data-color="{{ $isPositive ? '#10B981' : '#F43F5E' }}">
                                </canvas>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Static Metrics (2 cols) --}}
                <div class="md:col-span-2 lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700 flex items-center gap-3">
                        <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-md shrink-0">
                            <span class="text-xl">📦</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Items Sold</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($itemsSold ?? 0) }}</p>
                        </div>
                    </div>

                    <div class="p-4 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-md text-white flex items-center gap-3">
                        <div class="p-2 bg-white/20 rounded-md backdrop-blur-sm shrink-0">
                            <span class="text-xl">👥</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-indigo-100">Unique Buyers</p>
                            <p class="text-xl font-bold text-white">{{ number_format($uniqueBuyers ?? 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. PRODUCT STATUS SUMMARY (Compact Grid) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Inventory --}}
                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-3 text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        Product Status
                    </h3>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="p-2 bg-gray-50 rounded dark:bg-gray-700">
                            <p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Total</p>
                            <p class="text-base font-bold text-gray-900 dark:text-white">{{ $totalProducts ?? 0 }}</p>
                        </div>
                        <div class="p-2 bg-emerald-50 rounded dark:bg-emerald-900/20">
                            <p class="text-[10px] uppercase text-emerald-600 dark:text-emerald-400">Published</p>
                            <p class="text-base font-bold text-emerald-700 dark:text-emerald-300">{{ $publishedProducts ?? 0 }}</p>
                        </div>
                        <div class="p-2 bg-red-50 rounded dark:bg-red-900/20">
                            <p class="text-[10px] uppercase text-red-600 dark:text-red-400">Low Stock</p>
                            <p class="text-base font-bold text-red-700 dark:text-red-300">{{ $lowStockCount ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                {{-- Customer Stats --}}
                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-3 text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        Customer Growth
                    </h3>
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="p-2 bg-gray-50 rounded dark:bg-gray-700">
                            <p class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Total</p>
                            <p class="text-base font-bold text-gray-900 dark:text-white">{{ $totalCustomers ?? 0 }}</p>
                        </div>
                        <div class="p-2 bg-blue-50 rounded dark:bg-blue-900/20">
                            <p class="text-[10px] uppercase text-blue-600 dark:text-blue-400">New ({{ $rangeLabel }})</p>
                            <p class="text-base font-bold text-blue-700 dark:text-blue-300">{{ $newCustomersInRange ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. PRIMARY CHARTS (Reduced Height) --}}
            <section class="space-y-4">
                {{-- Sales Trend (Area Chart) --}}
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Sales Performance</h3>
                        <span class="text-[10px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded">{{ $rangeLabel }}</span>
                    </div>
                    <div class="h-64 w-full"> {{-- Reduced from h-80 --}}
                        <canvas id="salesByDayChart"></canvas>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {{-- Material Breakdown --}}
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-2 text-sm font-bold text-gray-900 dark:text-white">Product Material</h3>
                        <div class="h-56 flex justify-center"> {{-- Reduced Height --}}
                            <canvas id="materialChart"></canvas>
                        </div>
                    </div>

                    {{-- Revenue By Category --}}
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="mb-2 text-sm font-bold text-gray-900 dark:text-white">Revenue by Category</h3>
                        <div class="h-56"> {{-- Reduced Height --}}
                            <canvas id="revenueByCategoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 5. SECONDARY CHARTS (Reduced Height) --}}
            <section class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-2 text-sm font-bold text-gray-900 dark:text-white">Top Products</h3>
                    <div class="h-64"> {{-- Reduced Height --}}
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-2 text-sm font-bold text-gray-900 dark:text-white">Staff Performance</h3>
                    <div class="h-64"> {{-- Reduced Height --}}
                        <canvas id="staffSalesChart"></canvas>
                    </div>
                </div>
            </section>

            {{-- 6. STATUS DOUGHNUTS (Small) --}}
            <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach([
                    ['id' => 'pawnStatusChart', 'title' => 'Pawn Status'],
                    ['id' => 'repairStatusChart', 'title' => 'Repair Status'],
                    ['id' => 'mostFavoritedChart', 'title' => 'Most Favorited'],
                    ['id' => 'mostViewedChart', 'title' => 'Most Viewed']
                ] as $chart)
                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="mb-2 text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wide">{{ $chart['title'] }}</h3>
                    <div class="h-32 relative"> {{-- Significantly Reduced Height (h-32) --}}
                        <canvas id="{{ $chart['id'] }}"></canvas>
                    </div>
                </div>
                @endforeach
            </section>

            {{-- 7. MARKET BASKET --}}
            <section class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Frequently Bought Together</h3>
                    <span class="text-[10px] text-gray-500">Min: {{ $minSupport ?? 5 }}</span>
                </div>
                <div class="h-48"> {{-- Reduced Height --}}
                    <canvas id="frequentCombosChart"></canvas>
                </div>
            </section>

             {{-- 8. QUICK ACTIONS --}}
             <section class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">🚀 Quick Actions</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($quickActions as $action)
                        <a href="{{ $action['href'] }}"
                           class="flex items-center justify-between p-3 text-xs bg-gray-50 rounded border border-gray-100 hover:bg-indigo-50 hover:border-indigo-100 transition-all dark:bg-gray-900 dark:border-gray-700 dark:hover:bg-gray-700">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $action['label'] }}</p>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $action['description'] }}</p>
                            </div>
                            <span class="text-gray-400">→</span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.5.1/flowbite.min.js"></script>

    {{-- CHART.JS LOGIC --}}
    <script>
        // --- Configuration ---
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
        Chart.defaults.font.size = 11; // Smaller default font
        Chart.defaults.color = '#6B7280';
        Chart.defaults.scale.grid.color = 'rgba(229, 231, 235, 0.5)';
        Chart.defaults.scale.grid.borderDash = [4, 4];

        const COLORS = {
            primary: 'rgb(79, 70, 229)', // Indigo
            secondary: 'rgb(16, 185, 129)', // Emerald
            purple: 'rgb(139, 92, 246)',
            palette: ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899']
        };

        const pesoFormatter = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }); // Removed cents for compactness

        function createGradient(ctx, startColor, endColor) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, startColor);
            gradient.addColorStop(1, endColor);
            return gradient;
        }

        // --- Generic Render Function ---
        function renderChart(elementId, type, data, customOptions = {}) {
            const ctx = document.getElementById(elementId);
            if (!ctx) return;

            if (window.analyticsCharts && window.analyticsCharts[elementId]) {
                window.analyticsCharts[elementId].destroy();
            }
            if (!window.analyticsCharts) window.analyticsCharts = {};

            const defaultOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        padding: 8,
                        cornerRadius: 4,
                        displayColors: false,
                        titleFont: { size: 11 },
                        bodyFont: { size: 11 }
                    }
                }
            };

            const options = { ...defaultOptions, ...customOptions };
            if(customOptions.scales) options.scales = { ...defaultOptions.scales, ...customOptions.scales };
            if(customOptions.plugins) options.plugins = { ...defaultOptions.plugins, ...customOptions.plugins };

            window.analyticsCharts[elementId] = new Chart(ctx, { type, data, options });
        }

        document.addEventListener('DOMContentLoaded', function () {

            // 1. Sparklines (Thinner line, no dots)
            document.querySelectorAll('[id^="mini-chart-"]').forEach(ctx => {
                const data = JSON.parse(ctx.getAttribute('data-data') || '[]');
                const label = ctx.getAttribute('data-label');
                const color = ctx.getAttribute('data-color');
                if (data.length > 0) {
                    renderChart(ctx.id, 'line', {
                        labels: data.map((_, i) => i),
                        datasets: [{
                            data: data,
                            borderColor: color,
                            borderWidth: 1.5, // Thinner
                            pointRadius: 0,
                            tension: 0.4
                        }]
                    }, { scales: { x: { display: false }, y: { display: false } }, plugins: { tooltip: { enabled: false } } });
                }
            });

            // 2. Sales Trend (Gradient Area)
            const salesCtx = document.getElementById('salesByDayChart');
            if (salesCtx) {
                const ctx2d = salesCtx.getContext('2d');
                renderChart('salesByDayChart', 'line', {
                    labels: @json($salesByDayLabels),
                    datasets: [{
                        label: 'Revenue',
                        data: @json($salesByDayData),
                        borderColor: COLORS.primary,
                        backgroundColor: createGradient(ctx2d, 'rgba(79, 70, 229, 0.4)', 'rgba(79, 70, 229, 0.0)'),
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0, // No points by default
                        pointHoverRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: COLORS.primary
                    }]
                }, {
                    scales: {
                        y: {
                            ticks: { callback: (val) => pesoFormatter.format(val), font: { size: 10 } },
                            grid: { borderDash: [4, 4] }
                        },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    },
                    plugins: {
                        tooltip: { callbacks: { label: (c) => 'Revenue: ' + pesoFormatter.format(c.raw) } }
                    }
                });
            }

            // 3. Materials (Doughnut)
            renderChart('materialChart', 'doughnut', {
                labels: @json($materialLabels),
                datasets: [{
                    data: @json($materialData),
                    backgroundColor: COLORS.palette,
                    borderWidth: 0
                }]
            }, {
                cutout: '75%', // Thinner ring
                plugins: { legend: { display: true, position: 'right', labels: { usePointStyle: true, boxWidth: 6, font: {size: 10} } } }
            });

            // 4. Categories (Horizontal Bar)
            renderChart('revenueByCategoryChart', 'bar', {
                labels: @json($revenueByCategoryLabels),
                datasets: [{
                    label: 'Revenue',
                    data: @json($revenueByCategoryData),
                    backgroundColor: COLORS.secondary,
                    borderRadius: 3,
                    barPercentage: 0.7
                }]
            }, {
                indexAxis: 'y',
                scales: {
                    x: { ticks: { callback: (val) => '₱' + val.toLocaleString(), font: { size: 10 } } },
                    y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            });

            // 5. Top Products
            renderChart('topProductsChart', 'bar', {
                labels: @json($topProducts->pluck('name')),
                datasets: [{
                    label: 'Revenue',
                    data: @json($topProducts->pluck('total_sales')),
                    backgroundColor: COLORS.primary,
                    borderRadius: 3,
                    barPercentage: 0.7
                }]
            }, {
                indexAxis: 'y',
                scales: {
                    x: { ticks: { callback: (val) => '₱' + val.toLocaleString(), font: { size: 10 } } },
                    y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            });

            // 6. Staff Sales
            renderChart('staffSalesChart', 'bar', {
                labels: @json($staffSalesLabels),
                datasets: [{
                    label: 'Sales',
                    data: @json($staffSalesData),
                    backgroundColor: COLORS.purple,
                    borderRadius: 3,
                    barPercentage: 0.7
                }]
            }, {
                indexAxis: 'y',
                scales: { x: { ticks: { callback: (val) => '₱' + val.toLocaleString(), font: { size: 10 } } }, y: { ticks: { font: { size: 10 } } } }
            });

            // 7. Status Doughnuts (Small)
            const doughnutOptions = { cutout: '65%', plugins: { legend: { display: true, position: 'right', labels: { boxWidth: 6, font: {size: 9} } } } };
            const barSimpleOptions = { indexAxis: 'y', scales: { x: { display: false }, y: { grid: { display: false }, ticks: { font: { size: 9 } } } } };

            renderChart('pawnStatusChart', 'doughnut', { labels: @json($pawnStatusLabels), datasets: [{ data: @json($pawnStatusData), backgroundColor: [COLORS.primary, '#FBBF24', '#EF4444', '#10B981'], borderWidth: 0 }] }, doughnutOptions);
            renderChart('repairStatusChart', 'doughnut', { labels: @json($repairStatusLabels), datasets: [{ data: @json($repairStatusData), backgroundColor: [COLORS.primary, '#FBBF24', '#EF4444'], borderWidth: 0 }] }, doughnutOptions);

            renderChart('mostFavoritedChart', 'bar', {
                labels: @json($mostFavorited->pluck('name')),
                datasets: [{ data: @json($mostFavorited->pluck('total_favorites')), backgroundColor: '#EC4899', borderRadius: 3, barPercentage: 0.8 }]
            }, barSimpleOptions);

            renderChart('mostViewedChart', 'bar', {
                labels: @json($mostViewed->pluck('name')),
                datasets: [{ data: @json($mostViewed->pluck('views')), backgroundColor: '#8B5CF6', borderRadius: 3, barPercentage: 0.8 }]
            }, barSimpleOptions);

            // 8. Combos
            renderChart('frequentCombosChart', 'bar', {
                labels: @json($frequentComboLabels ?? []),
                datasets: [{
                    data: @json($frequentComboSupport ?? []),
                    backgroundColor: COLORS.primary,
                    borderRadius: 3,
                    barPercentage: 0.6
                }]
            }, {
                indexAxis: 'y',
                scales: { x: { ticks: { stepSize: 1, font: { size: 10 } } }, y: { grid: { display: false }, ticks: { font: { size: 10 } } } }
            });
        });
    </script>
</x-admin-layout>
