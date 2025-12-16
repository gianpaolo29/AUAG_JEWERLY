<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Market Dashboard') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Real-time gold market insights and forecasts</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs bg-violet-100 text-violet-800 px-3 py-1 rounded-full font-medium">
                    Live Data
                </span>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            </div>
        </div>
    </x-slot>

    @php
        $points = collect(data_get($gold, 'history_points', []));
        $table = $points->take(-14)->reverse()->values();

        // Calculate metrics for matrix
        $currentPrice = data_get($gold, 'current_php_per_gram', 0);
        $forecastPrice = data_get($gold, 'next_php_per_gram', 0);
        $trend = 'stable';
        $momentum = 'neutral';
        $volatility = 'low';
        $confidence = 'medium';

        if ($points->count() >= 2) {
            $recentPoints = $points->take(-5);
            if ($recentPoints->count() >= 2) {
                $firstRecent = $recentPoints->first()['y'] ?? 0;
                $lastRecent = $recentPoints->last()['y'] ?? 0;
                $recentChange = $lastRecent - $firstRecent;

                if ($recentChange > 5) $trend = 'bullish';
                elseif ($recentChange < -5) $trend = 'bearish';
                else $trend = 'stable';

                // Calculate momentum (acceleration/deceleration)
                $midPoint = $recentPoints->slice(1, 1)->first()['y'] ?? 0;
                $firstHalfChange = $midPoint - $firstRecent;
                $secondHalfChange = $lastRecent - $midPoint;

                if ($secondHalfChange > $firstHalfChange) $momentum = 'accelerating';
                elseif ($secondHalfChange < $firstHalfChange) $momentum = 'decelerating';
                else $momentum = 'neutral';
            }

            // Calculate volatility (standard deviation of last 10 points)
            $last10 = $points->take(-10);
            if ($last10->count() >= 3) {
                $values = $last10->pluck('y')->toArray();
                $mean = array_sum($values) / count($values);
                $variance = 0;
                foreach ($values as $value) {
                    $variance += pow($value - $mean, 2);
                }
                $stdDev = sqrt($variance / count($values));
                $cv = ($mean > 0) ? ($stdDev / $mean) * 100 : 0;

                if ($cv > 3) $volatility = 'high';
                elseif ($cv > 1.5) $volatility = 'medium';
                else $volatility = 'low';
            }

            // Confidence based on forecast difference
            if ($currentPrice > 0 && $forecastPrice > 0) {
                $changePercent = abs(($forecastPrice - $currentPrice) / $currentPrice * 100);
                if ($changePercent < 2) $confidence = 'high';
                elseif ($changePercent < 5) $confidence = 'medium';
                else $confidence = 'low';
            }
        }
    @endphp

    <div class="py-8 bg-gradient-to-br from-gray-50 to-violet-50/30 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- SUMMARY CARDS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Current Price Card --}}
                <div class="bg-white rounded-2xl border border-violet-200/60 p-6 shadow-lg shadow-violet-100/50 hover:shadow-violet-200/50 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-sm font-semibold text-violet-600 uppercase tracking-wide">Current Price</div>
                            <div class="text-xs text-gray-500 mt-1">Latest market rate</div>
                        </div>
                        <div class="w-10 h-10 bg-violet-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1">
                        {{ $currentPrice > 0 ? '₱'.number_format($currentPrice,2) : '—' }}
                    </div>
                    <div class="text-sm text-gray-600">
                        per gram
                    </div>
                    @if(data_get($gold,'current_date'))
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ \Carbon\Carbon::parse(data_get($gold,'current_date'))->format('M d, Y') }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Forecast Card --}}
                <div class="bg-white rounded-2xl border border-violet-200/60 p-6 shadow-lg shadow-violet-100/50 hover:shadow-violet-200/50 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-sm font-semibold text-violet-600 uppercase tracking-wide">Next Forecast</div>
                            <div class="text-xs text-gray-500 mt-1">Predicted upcoming rate</div>
                        </div>
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1">
                        {{ $forecastPrice > 0 ? '₱'.number_format($forecastPrice,2) : '—' }}
                    </div>
                    <div class="text-sm text-gray-600">
                        forecasted per gram
                    </div>
                    @if(data_get($gold,'next_date'))
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ \Carbon\Carbon::parse(data_get($gold,'next_date'))->format('M d, Y') }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- MARKET MATRIX Card --}}
            </div>

            {{-- NOTE BANNER --}}
            @if(data_get($gold,'note'))
                <div class="bg-gradient-to-r from-violet-50 to-indigo-50 border border-violet-200 rounded-xl p-5 shadow-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mr-3">
                            <svg class="w-5 h-5 text-violet-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-violet-800 mb-1">Market Insight</h4>
                            <p class="text-gray-700 text-sm">{{ data_get($gold,'note') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- PERFORMANCE METRICS --}}
            @if($points->count() >= 2)
                @php
                    $firstPrice = $points->first()['y'] ?? 0;
                    $lastPrice = $points->last()['y'] ?? 0;
                    $change = $lastPrice - $firstPrice;
                    $changePercent = $firstPrice > 0 ? ($change / $firstPrice) * 100 : 0;
                    $isPositive = $change >= 0;

                    // Calculate 7-day performance
                    $recentPoints = $points->take(-7);
                    $recentFirst = $recentPoints->first()['y'] ?? 0;
                    $recentLast = $recentPoints->last()['y'] ?? 0;
                    $recentChange = $recentLast - $recentFirst;
                    $recentChangePercent = $recentFirst > 0 ? ($recentChange / $recentFirst) * 100 : 0;
                @endphp

            @endif

            {{-- HISTORY CHART --}}
            <div class="bg-white rounded-2xl border border-violet-200/60 p-6 shadow-lg shadow-violet-100/50">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Gold Price History</h3>
                        <p class="text-sm text-gray-600 mt-1">Historical trends in Philippine Peso per gram</p>
                    </div>
                    <div class="text-xs bg-violet-100 text-violet-700 px-3 py-1.5 rounded-full font-medium">
                        {{ $points->count() }} data points
                    </div>
                </div>

                <div class="h-80">
                    <canvas id="goldHistoryChart"></canvas>
                </div>
            </div>

            {{-- HISTORY TABLE --}}
            <div class="bg-white rounded-2xl border border-violet-200/60 shadow-lg shadow-violet-100/50 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-violet-50/30">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Recent Gold Prices</h3>
                            <p class="text-sm text-gray-600 mt-1">Last 14 trading days</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="text-xs text-gray-500">
                                <span class="inline-block w-2 h-2 bg-violet-500 rounded-full mr-1"></span>
                                ₱/gram
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-violet-50/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-violet-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Date
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-violet-700 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Price (₱/g)
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        @forelse($table as $row)
                            <tr class="hover:bg-violet-50/30 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($row['x'])->format('D, M d, Y') }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-bold text-violet-700">
                                        ₱{{ number_format((float)$row['y'], 2) }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center" colspan="2">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-gray-500 text-sm">No historical data available</p>
                                        <p class="text-gray-400 text-xs mt-1">Sync data in the Gold Dashboard</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- FOOTER NOTE --}}
            <div class="text-center text-xs text-gray-500 pt-4 pb-8">
                <p>Data updates automatically • Last refreshed: {{ now()->format('M d, Y H:i') }}</p>
            </div>

        </div>
    </div>

    <script>
        const points = @json(data_get($gold, 'history_points', []));
        const labels = points.map(p => p.x);
        const values = points.map(p => p.y);

        const ctx = document.getElementById('goldHistoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.map(d => {
                    const dt = new Date(d);
                    return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'Gold Price (₱/gram)',
                    data: values,
                    tension: 0.3,
                    borderWidth: 3,
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.08)',
                    pointBackgroundColor: '#7c3aed',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    cubicInterpolationMode: 'monotone'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#1f2937',
                        bodyColor: '#4f46e5',
                        borderColor: '#e5e7eb',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 6,
                        usePointStyle: true,
                        callbacks: {
                            label: (ctx) => {
                                return `₱${Number(ctx.parsed.y).toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })} per gram`;
                            },
                            title: (items) => {
                                const date = new Date(labels[items[0].dataIndex]);
                                return date.toLocaleDateString('en-US', {
                                    weekday: 'short',
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(229, 231, 235, 0.5)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 11
                            },
                            callback: (v) => '₱' + Number(v).toLocaleString()
                        },
                        beginAtZero: false
                    }
                }
            }
        });
    </script>
</x-app-layout>
