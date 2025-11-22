<x-admin-layout title="Dashboard">
    <div class="space-y-6">

        <div class="grid gap-6 md:grid-cols-4">
            <div class="bg-white rounded-2xl shadow p-5 border border-gray-100">
                <p class="text-xs font-medium text-gray-500">Total Revenue</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">₱{{ number_format($totalRevenue, 2) }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow p-5 border border-gray-100">
                <p class="text-xs font-medium text-gray-500">Today Revenue</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">₱{{ number_format($todayRevenue, 2) }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow p-5 border border-gray-100">
                <p class="text-xs font-medium text-gray-500">This Month Revenue</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">₱{{ number_format($monthlyRevenue, 2) }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow p-5 border border-gray-100">
                <p class="text-xs font-medium text-gray-500">Active Pawn Value</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">₱{{ number_format($totalPawnValue, 2) }}</p>
                <p class="mt-1 text-xs text-gray-500">Repairs completed: {{ $totalRepairsCompleted }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="bg-white rounded-2xl shadow p-6 border border-gray-100 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-semibold text-gray-900">Revenue (Last 7 Days)</p>
                </div>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
                <p class="text-sm font-semibold text-gray-900 mb-4">Pawn Status</p>
                <div class="h-64">
                    <canvas id="pawnStatusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="bg-white rounded-2xl shadow p-6 border border-gray-100 lg:col-span-1">
                <p class="text-sm font-semibold text-gray-900 mb-4">Repair Status</p>
                <div class="h-64">
                    <canvas id="repairStatusChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow p-6 border border-gray-100 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-semibold text-gray-900">Recent Transactions</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">Customer</th>
                                <th class="px-4 py-2 text-left">Staff</th>
                                <th class="px-4 py-2 text-left">Type</th>
                                <th class="px-4 py-2 text-right">Total</th>
                                <th class="px-4 py-2 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentTransactions as $t)
                                <tr>
                                    <td class="px-4 py-2 text-xs font-mono text-gray-500">#{{ $t->id }}</td>
                                    <td class="px-4 py-2">
                                        <p class="text-sm text-gray-900">{{ $t->customer->name ?? 'Walk-in' }}</p>
                                    </td>
                                    <td class="px-4 py-2">
                                        <p class="text-sm text-gray-900">{{ $t->staff->name ?? '—' }}</p>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold
                                            @if($t->type === 'Buy') bg-emerald-50 text-emerald-700 border-emerald-100
                                            @elseif($t->type === 'Pawn') bg-amber-50 text-amber-700 border-amber-100
                                            @else bg-sky-50 text-sky-700 border-sky-100 @endif">
                                            {{ $t->type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        ₱{{ number_format($t->items->sum('line_total'), 2) }}
                                    </td>
                                    <td class="px-4 py-2 text-right text-xs text-gray-500">
                                        {{ $t->created_at->format('M d, Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                        No transactions yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($revenueChartLabels),
                datasets: [{
                    data: @json($revenueChartData),
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { font: { size: 11 } } },
                    x: { ticks: { font: { size: 11 } } }
                }
            }
        });

        const pawnCtx = document.getElementById('pawnStatusChart').getContext('2d');
        new Chart(pawnCtx, {
            type: 'doughnut',
            data: {
                labels: @json($pawnStatusLabels),
                datasets: [{
                    data: @json($pawnStatusData),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
            }
        });

        const repairCtx = document.getElementById('repairStatusChart').getContext('2d');
        new Chart(repairCtx, {
            type: 'bar',
            data: {
                labels: @json($repairStatusLabels),
                datasets: [{
                    data: @json($repairStatusData),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { font: { size: 11 } } },
                    x: { ticks: { font: { size: 11 } } }
                }
            }
        });
    </script>
</x-admin-layout>
