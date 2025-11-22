<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\PawnItem;
use App\Models\Repair;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = DB::table('transaction_items')->sum('line_total');

        $todayRevenue = DB::table('transaction_items')
            ->whereDate('created_at', Carbon::today())
            ->sum('line_total');

        $monthlyRevenue = DB::table('transaction_items')
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('line_total');

        $totalPawnValue = PawnItem::where('status', 'active')->sum('price');

        $totalRepairsCompleted = Repair::where('status', 'completed')->count();

        $revenueRows = DB::table('transaction_items')
            ->selectRaw('DATE(created_at) as date, SUM(line_total) as total')
            ->whereDate('created_at', '>=', Carbon::today()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $revenueChartLabels = [];
        $revenueChartData   = [];
        $datesRange = collect(range(6, 0))->map(fn ($d) => Carbon::today()->subDays($d)->toDateString());

        $revenueByDate = $revenueRows->keyBy('date');

        foreach ($datesRange as $date) {
            $revenueChartLabels[] = Carbon::parse($date)->format('M d');
            $revenueChartData[]   = (float) optional($revenueByDate->get($date))->total ?? 0;
        }

        $pawnStatusRows = PawnItem::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $pawnStatusLabels = $pawnStatusRows->pluck('status')->map(fn ($s) => ucfirst($s))->toArray();
        $pawnStatusData   = $pawnStatusRows->pluck('total')->map(fn ($v) => (int) $v)->toArray();

        $repairStatusRows = Repair::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $repairStatusLabels = $repairStatusRows->pluck('status')->map(fn ($s) => ucfirst($s))->toArray();
        $repairStatusData   = $repairStatusRows->pluck('total')->map(fn ($v) => (int) $v)->toArray();

        $recentTransactions = Transaction::with(['customer:id,name', 'staff:id,name', 'items'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'todayRevenue',
            'monthlyRevenue',
            'totalPawnValue',
            'totalRepairsCompleted',
            'revenueChartLabels',
            'revenueChartData',
            'pawnStatusLabels',
            'pawnStatusData',
            'repairStatusLabels',
            'repairStatusData',
            'recentTransactions'
        ));
    }
}
