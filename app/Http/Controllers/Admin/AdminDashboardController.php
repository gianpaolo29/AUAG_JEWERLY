<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\PawnItem;
use App\Models\Repair;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $yesterday = (clone $today)->subDay();

        // -----------------------
        // REVENUE TOTALS
        // -----------------------
        $totalRevenue = DB::table('transaction_items')->sum('line_total');

        $todayRevenue = DB::table('transaction_items')
            ->whereDate('created_at', $today)
            ->sum('line_total');

        $yesterdayRevenue = DB::table('transaction_items')
            ->whereDate('created_at', $yesterday)
            ->sum('line_total');

        $monthlyRevenue = DB::table('transaction_items')
            ->whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month)
            ->sum('line_total');


            $lastMonth = Carbon::now()->copy()->subMonthNoOverflow();

        $lastMonthRevenue = DB::table('transaction_items')
            ->whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->sum('line_total');

        // --- % CHANGE VS LAST MONTH ---
        // Formula: ($thisMonth - $lastMonth) / max($lastMonth, 1) * 100
        $monthlyChange = 0;
        if ($monthlyRevenue > 0 || $lastMonthRevenue > 0) {
            $monthlyChange = (($monthlyRevenue - $lastMonthRevenue) / max($lastMonthRevenue, 1)) * 100;
        }

        // -----------------------
        // PAWN / REPAIRS / OTHER COUNTS
        // -----------------------
        $totalPawnValue = PawnItem::where('status', 'active')->sum('price');
        $totalRepairsCompleted = Repair::where('status', 'completed')->count();

        $totalCustomers = Customer::count();
        $lowStockProducts = Product::where('quantity', '<=', 5)->count();

        // -----------------------
        // REVENUE CHART (LAST 30 DAYS)
        // -----------------------
        $startDate = (clone $today)->subDays(29);

        $revenueRows = DB::table('transaction_items')
            ->selectRaw('DATE(created_at) as date, SUM(line_total) as total')
            ->whereDate('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Build a continuous 30-day range
        $datesRange = collect();
        for ($d = 0; $d < 30; $d++) {
            $datesRange->push((clone $startDate)->addDays($d)->toDateString());
        }

        $revenueByDate = $revenueRows->keyBy('date');

        $revenueChartLabels = [];
        $revenueChartData = [];

        foreach ($datesRange as $date) {
            $revenueChartLabels[] = Carbon::parse($date)->format('M d');
            $value = $revenueByDate->has($date)
                ? (float) $revenueByDate->get($date)->total
                : 0.0;
            $revenueChartData[] = $value;
        }

        // -----------------------
        // PAWN STATUS CHART
        // -----------------------
        $statusCounts = PawnItem::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Fix the order so it matches your color legend in JS
        $pawnStatusOrder = ['active', 'redeemed', 'forfeited', 'expired'];

        $pawnStatusLabels = [];
        $pawnStatusData = [];

        foreach ($pawnStatusOrder as $status) {
            $label = $status === 'forfeited' ? 'Forfeited' : ucfirst($status);
            $pawnStatusLabels[] = $label;
            $pawnStatusData[] = (int) ($statusCounts[$status] ?? 0);
        }

        $activePawnCount = (int) ($statusCounts['active'] ?? 0);

        // -----------------------
        // REPAIR STATUS (kept for future use)
        // -----------------------
        $repairStatusRows = Repair::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $repairStatusLabels = $repairStatusRows->pluck('status')
            ->map(fn ($s) => ucfirst($s))
            ->toArray();

        $repairStatusData = $repairStatusRows->pluck('total')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        // -----------------------
        // RECENT TRANSACTIONS
        // -----------------------
        $recentTransactions = Transaction::with([
                // include contact_no explicitly
                'customer:id,name,contact_no',
                'items',
            ])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'todayRevenue',
            'yesterdayRevenue',
            'monthlyRevenue',
            'monthlyChange',
            'totalPawnValue',
            'totalRepairsCompleted',
            'totalCustomers',
            'lowStockProducts',
            'revenueChartLabels',
            'revenueChartData',
            'pawnStatusLabels',
            'pawnStatusData',
            'activePawnCount',
            'repairStatusLabels',
            'repairStatusData',
            'recentTransactions'
        ));
    }
}
