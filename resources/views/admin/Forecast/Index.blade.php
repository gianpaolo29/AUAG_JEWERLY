<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gold Forecast') }} 📊
        </h2>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </x-slot>

    @php
        // These are already computed in the controller and passed to the view
        $historyPhpPoints = $historyPhpPoints ?? collect();
        $forecastPhpPoints = $forecastPhpPoints ?? collect();

        $modelPath = $lastRun?->model_path ?? null;
        $modelFileExists = $modelPath ? file_exists($modelPath) : false;

        $usdToPhp = $usdToPhp ?? 56.00;
        $fxDate = $fxDate ?? date('Y-m-d');

        $latestPhpPerGram = $latestPhpPerGram ?? null;
        $change = $change ?? null;
        $changePct = $changePct ?? null;

        $TROY_OZ_TO_GRAMS = 31.1034768;
    @endphp


    <div class="py-6 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header with Actions -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gold Price Forecast</h1>
                    <p class="text-gray-600">Historical + forecast (next {{ $days }} days)</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('gold.sync') }}">
                        @csrf
                        <button class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition duration-150 ease-in-out" type="submit">
                            1) Sync Data
                        </button>
                    </form>
                    <form method="POST" action="{{ route('gold.train') }}">
                        @csrf
                        <button class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition duration-150 ease-in-out" type="submit">
                            2) Train Model
                        </button>
                    </form>
                    <form method="POST" action="{{ route('gold.forecast') }}">
                        @csrf
                        <input type="hidden" name="days" value="{{ $days }}">
                        <button class="px-4 py-2 bg-violet-600 border border-violet-700 rounded-lg text-white font-medium hover:bg-violet-700 transition duration-150 ease-in-out" type="submit">
                            3) Generate Forecast
                        </button>
                    </form>
                </div>
            </div>

            <!-- Status Messages -->
            @if(session('status'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                    {{ session('status') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Horizon Selector -->
            <div class="mb-8">
                <p class="text-gray-600 mb-2">Select forecast horizon:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach([7, 14, 30] as $d)
                        <a href="{{ route('admin.forecast', ['days' => $d]) }}"
                           class="px-4 py-2 rounded-full border {{ $days === $d ? 'bg-violet-100 border-violet-300 text-violet-700' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }} transition duration-150 ease-in-out">
                            {{ $d }} days
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Latest Price Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <p class="text-gray-600 text-sm font-medium mb-1">Latest Price</p>
                    <div class="text-2xl font-extrabold text-gray-900 mb-2">
                        @if($latestPhpPerGram !== null)
                            ₱{{ number_format($latestPhpPerGram, 2) }} / g
                        @else
                            —
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 mb-2">
                        FX: 1 USD = ₱{{ number_format($usdToPhp, 4) }} ({{ $fxDate }})
                    </div>
                    <p class="text-gray-500 text-sm">
                        @if($latest ?? null)
                            Date: {{ \Carbon\Carbon::parse($latest->date)->format('M d, Y') }}
                        @endif
                    </p>
                </div>

                <!-- Daily Change Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <p class="text-gray-600 text-sm font-medium mb-1">Daily Change</p>
                    <div class="text-3xl font-bold mb-2">
                        @if($change !== null)
                            <span class="{{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $change >= 0 ? '+' : '' }}₱{{ number_format((float)$change, 2) }} / g
                            </span>
                        @else
                            <span class="text-gray-900">—</span>
                        @endif
                    </div>
                    <p class="text-gray-500 text-sm">
                        @if($changePct !== null)
                            {{ $changePct >= 0 ? '+' : '' }}{{ number_format((float)$changePct, 2) }}%
                        @endif
                    </p>
                </div>

                <!-- Model Status Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <p class="text-gray-600 text-sm font-medium mb-1">Model Status</p>
                    @if($lastRun)
                        <div class="font-bold text-gray-900 mb-1">Run #{{ $lastRun->id }}</div>
                        <p class="text-gray-500 text-sm mb-1">
                            Trained: {{ \Carbon\Carbon::parse($lastRun->trained_at)->format('M d, Y H:i') }}
                        </p>
                        <p class="text-gray-500 text-sm mb-3">
                            Lookback: {{ $lastRun->lookback }} days ·
                            Samples: {{ data_get($lastRun->metrics, 'samples', '—') }}
                        </p>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $modelFileExists ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $modelFileExists ? '✓ Model file exists' : '✗ Model file missing' }}
                            </span>
                        </div>
                    @else
                        <div class="text-gray-500">No trained model yet.</div>
                    @endif
                </div>
            </div>

            <!-- Chart Section -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">History + Forecast (PHP per gram)</h3>
                <div class="h-96">
                    <canvas id="goldChart"></canvas>
                </div>
                <p class="text-gray-500 text-sm mt-4">Forecast line is dashed</p>
            </div>

            <!-- Forecast Table -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Forecast Table</h3>
                </div>

                @if($forecast->count() === 0)
                    <div class="p-6 text-center text-gray-500">
                        No forecast points yet. Run Sync → Train → Forecast.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Predicted (PHP/g)</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($forecast as $f)
                                @php
                                    $predictedUsd = (float) ($f->predicted_usd ?? 0);

                                    // Convert USD/oz -> PHP/gram using Troy oz = 31.1034768g
                                    $predictedPhpPerGram = $predictedUsd > 0
                                        ? (($predictedUsd * $usdToPhp) / $TROY_OZ_TO_GRAMS)
                                        : 0;

                                    $lowerUsd = $f->lower_usd !== null ? (float) $f->lower_usd : null;
                                    $upperUsd = $f->upper_usd !== null ? (float) $f->upper_usd : null;

                                    $range = ($lowerUsd !== null && $upperUsd !== null) ? ($upperUsd - $lowerUsd) : null;
                                    $confidence = ($range !== null && $predictedUsd > 0)
                                        ? max(0, min(100, (1 - ($range / $predictedUsd)) * 100))
                                        : null;
                                @endphp

                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($f->target_date)->format('M d, Y') }}
                                    </td>


                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-green-700">
                                            ₱{{ number_format($predictedPhpPerGram, 2) }}
                                        </span>
                                        <div class="text-xs text-gray-500">per gram</div>
                                    </td>

                                </tr>
                            @endforeach

                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        const history = @json($historyPhpPoints);
        const forecast = @json($forecastPhpPoints);

        const labels = [...history.map(p => p.x), ...forecast.map(p => p.x)];
        const historyValues = labels.map((_, i) => i < history.length ? history[i].y : null);
        const forecastValues = labels.map((_, i) => i < history.length ? null : (forecast[i - history.length]?.y ?? null));

        const ctx = document.getElementById('goldChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.map(date => {
                    const d = new Date(date);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [
                    {
                        label: 'Historical (₱/g)',
                        data: historyValues,
                        borderColor: '#4f46e5', // Violet-600
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        tension: 0.25,
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: true
                    },
                    {
                        label: 'Forecast (₱/g)',
                        data: forecastValues,
                        borderColor: '#7c3aed', // Violet-700
                        backgroundColor: 'transparent',
                        tension: 0.25,
                        borderWidth: 2,
                        borderDash: [6, 6],
                        pointRadius: 0
                    }
                ]
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
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#374151',
                            font: {
                                size: 14
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#1f2937',
                        bodyColor: '#374151',
                        borderColor: '#e5e7eb',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label = label.replace('(₱/g)', '');
                                }
                                if (context.parsed.y !== null) {
                                    label += '₱' + context.parsed.y.toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(229, 231, 235, 0.5)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b7280',
                            maxRotation: 45
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(229, 231, 235, 0.5)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#6b7280',
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        },
                        beginAtZero: false
                    }
                }
            }
        });
    </script>
</x-admin-layout>
