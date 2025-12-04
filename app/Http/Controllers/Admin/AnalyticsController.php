<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\EclatService;
use App\Models\Product;

class AnalyticsController extends Controller
{
    public function index(Request $request, EclatService $eclat)
    {
        // --- DATE RANGE FILTER (for most charts & funnel) ---
        // accepted: today | 7d | 30d | all
        $range = $request->input('range', '30d');

        $rangeFilter = function ($query, $column = 'created_at') use ($range) {
            if ($range === 'today') {
                $query->whereDate($column, today());
            } elseif ($range === '7d') {
                $query->where($column, '>=', now()->subDays(6)->startOfDay());
            } elseif ($range === '30d') {
                $query->where($column, '>=', now()->subDays(29)->startOfDay());
            }
            // 'all' = no filter
            return $query;
        };

        $rangeLabel = match ($range) {
            'today' => 'Today',
            '7d'    => 'Last 7 Days',
            '30d'   => 'Last 30 Days',
            default => 'All Time',
        };

        // Helper: % change between current & previous
        $percentChange = function (float $current, float $previous): float {
            if ($previous > 0) {
                return round((($current - $previous) / $previous) * 100, 1);
            }
            return $current > 0 ? 100.0 : 0.0;
        };

        // Base revenue query (Buy transactions only)
        $revenueBaseQuery = function () {
            return DB::table('transactions')
                ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
                ->where('transactions.type', 'Buy');
        };

        // ----------------------------------------------------
        // TOP METRICS – TODAY / WEEK / MONTH + SPARKLINES
        // ----------------------------------------------------

        // TODAY vs YESTERDAY
        $todayStart     = now()->startOfDay();
        $todayEnd       = now()->endOfDay();
        $yesterdayStart = now()->subDay()->startOfDay();
        $yesterdayEnd   = now()->subDay()->endOfDay();

        $todayRevenue = $revenueBaseQuery()
            ->whereBetween('transactions.created_at', [$todayStart, $todayEnd])
            ->sum('transaction_items.line_total');

        $yesterdayRevenue = $revenueBaseQuery()
            ->whereBetween('transactions.created_at', [$yesterdayStart, $yesterdayEnd])
            ->sum('transaction_items.line_total');

        $todayRevenueChange = $percentChange($todayRevenue, $yesterdayRevenue);

        // Today sparkline (hourly)
        $todayRows = $revenueBaseQuery()
            ->whereBetween('transactions.created_at', [$todayStart, $todayEnd])
            ->selectRaw('HOUR(transactions.created_at) as hour, SUM(transaction_items.line_total) as total')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        $todayRevenueData = [];
        for ($h = 0; $h < 24; $h++) {
            $todayRevenueData[] = (float) ($todayRows[$h] ?? 0);
        }

        // THIS WEEK vs LAST WEEK (week-to-date)
        $weekStart      = now()->startOfWeek();
        $weekEnd        = now()->endOfDay();
        $prevWeekStart  = (clone $weekStart)->subWeek();
        $prevWeekEnd    = (clone $weekStart)->subDay();

        $weekRevenue = $revenueBaseQuery()
            ->whereBetween('transactions.created_at', [$weekStart, $weekEnd])
            ->sum('transaction_items.line_total');

        $prevWeekRevenue = $revenueBaseQuery()
            ->whereBetween('transactions.created_at', [$prevWeekStart, $prevWeekEnd])
            ->sum('transaction_items.line_total');

        $weekRevenueChange = $percentChange($weekRevenue, $prevWeekRevenue);

        // Week sparkline (current week, by day)
        $weekRows = $revenueBaseQuery()
            ->whereBetween('transactions.created_at', [$weekStart, $weekEnd])
            ->selectRaw('DATE(transactions.created_at) as day, SUM(transaction_items.line_total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $weekRevenueData = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i)->toDateString();
            $weekRevenueData[] = (float) ($weekRows[$day] ?? 0);
        }

        // THIS MONTH vs LAST MONTH (month-to-date)
        $monthStart      = now()->startOfMonth();
        $monthEnd        = now()->endOfDay();
        $prevMonthStart  = (clone $monthStart)->subMonth()->startOfMonth();
        $prevMonthEnd    = (clone $monthStart)->subDay();

        $monthRevenue = $revenueBaseQuery()
            ->whereBetween('transactions.created_at', [$monthStart, $monthEnd])
            ->sum('transaction_items.line_total');

        $prevMonthRevenue = $revenueBaseQuery()
            ->whereBetween('transactions.created_at', [$prevMonthStart, $prevMonthEnd])
            ->sum('transaction_items.line_total');

        $monthRevenueChange = $percentChange($monthRevenue, $prevMonthRevenue);

        // Month sparkline (last 30 days, by day)
        $last30Start = now()->subDays(29)->startOfDay();
        $last30End   = now()->endOfDay();

        $monthRows = $revenueBaseQuery()
            ->whereBetween('transactions.created_at', [$last30Start, $last30End])
            ->selectRaw('DATE(transactions.created_at) as day, SUM(transaction_items.line_total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $monthRevenueData = [];
        for ($i = 0; $i < 30; $i++) {
            $day = $last30Start->copy()->addDays($i)->toDateString();
            $monthRevenueData[] = (float) ($monthRows[$day] ?? 0);
        }

        // All-time total revenue
        $totalRevenue = $revenueBaseQuery()
            ->sum('transaction_items.line_total');

        // Buy count (all time) for overall AOV
        $buyCount = DB::table('transactions')
            ->where('type', 'Buy')
            ->count();

        $avgOrderValue = $buyCount > 0
            ? round($totalRevenue / $buyCount, 2)
            : 0;

        // ----------------------------------------------------
        // TOTAL ORDERS (range) + % CHANGE vs prior period
        // ----------------------------------------------------
        if ($range === 'all') {
            $totalOrders      = $buyCount;
            $totalOrdersChange = 0;
        } else {
            // derive [rangeStart, rangeEnd] and [prevRangeStart, prevRangeEnd]
            switch ($range) {
                case 'today':
                    $rangeStart      = $todayStart;
                    $rangeEnd        = $todayEnd;
                    $prevRangeStart  = $yesterdayStart;
                    $prevRangeEnd    = $yesterdayEnd;
                    break;

                case '7d':
                    $rangeStart      = now()->subDays(6)->startOfDay();
                    $rangeEnd        = now()->endOfDay();
                    $prevRangeStart  = now()->subDays(13)->startOfDay();
                    $prevRangeEnd    = now()->subDays(7)->endOfDay();
                    break;

                case '30d':
                default:
                    $rangeStart      = now()->subDays(29)->startOfDay();
                    $rangeEnd        = now()->endOfDay();
                    $prevRangeStart  = now()->subDays(59)->startOfDay();
                    $prevRangeEnd    = now()->subDays(30)->endOfDay();
                    break;
            }

            $totalOrders = DB::table('transactions')
                ->where('type', 'Buy')
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->count();

            $prevOrders = DB::table('transactions')
                ->where('type', 'Buy')
                ->whereBetween('created_at', [$prevRangeStart, $prevRangeEnd])
                ->count();

            $totalOrdersChange = $percentChange($totalOrders, $prevOrders);
        }

        // ----------------------------------------------------
        // SALES TREND (filtered by range)
        // ----------------------------------------------------
        $salesByDayQuery = $revenueBaseQuery()
            ->selectRaw('DATE(transactions.created_at) as day, SUM(transaction_items.line_total) as total')
            ->groupBy('day')
            ->orderBy('day');

        $rangeFilter($salesByDayQuery, 'transactions.created_at');

        $salesByDay       = $salesByDayQuery->get();
        $salesByDayLabels = $salesByDay->pluck('day')->map(fn($d) => Carbon::parse($d)->format('M d'));
        $salesByDayData   = $salesByDay->pluck('total');

        // ----------------------------------------------------
        // PRODUCT / INVENTORY ANALYTICS
        // ----------------------------------------------------
        $totalProducts = DB::table('products')->count();

        $publishedProducts = DB::table('products')
            ->where('status', 1)
            ->count();

        $lowStockCount = DB::table('products')
            ->where('quantity', '<', 5)
            ->count();

        // Material breakdown (all time)
        $materials = DB::table('products')
            ->selectRaw('material, COUNT(*) as count')
            ->whereNotNull('material')
            ->groupBy('material')
            ->get();

        $materialLabels = $materials->pluck('material');
        $materialData   = $materials->pluck('count');

        // Revenue per category (filtered by range)
        $revenueByCategoryQuery = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('transactions.type', 'Buy')
            ->groupBy('categories.name')
            ->selectRaw('COALESCE(categories.name, "Uncategorized") as category, SUM(transaction_items.line_total) as total_sales')
            ->orderByDesc('total_sales');

        $rangeFilter($revenueByCategoryQuery, 'transactions.created_at');

        $revenueByCategory       = $revenueByCategoryQuery->get();
        $revenueByCategoryLabels = $revenueByCategory->pluck('category');
        $revenueByCategoryData   = $revenueByCategory->pluck('total_sales');

        // Top-selling products (filtered by range)
        $topProductsQuery = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.type', 'Buy')
            ->groupBy('products.id', 'products.name')
            ->selectRaw('products.name, SUM(transaction_items.quantity) as total_qty, SUM(transaction_items.line_total) as total_sales')
            ->orderByDesc('total_sales')
            ->limit(5);

        $rangeFilter($topProductsQuery, 'transactions.created_at');

        $topProducts = $topProductsQuery->get();

        // ----------------------------------------------------
        // CUSTOMER ANALYTICS
        // ----------------------------------------------------
        $totalCustomers = DB::table('users')
            ->where('role', 'customer')
            ->count();

        $newCustomersThisMonth = DB::table('users')
            ->where('role', 'customer')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        // ----------------------------------------------------
        // PAWN / REPAIR STATUS (filtered by range)
        // ----------------------------------------------------
        $pawnStatusQuery = DB::table('pawn_items')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status');

        $rangeFilter($pawnStatusQuery, 'created_at');

        $pawnStatusRaw    = $pawnStatusQuery->get();
        $pawnStatusLabels = $pawnStatusRaw->pluck('status');
        $pawnStatusData   = $pawnStatusRaw->pluck('total');

        $repairStatusQuery = DB::table('repairs')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status');

        $rangeFilter($repairStatusQuery, 'created_at');

        $repairStatusRaw    = $repairStatusQuery->get();
        $repairStatusLabels = $repairStatusRaw->pluck('status');
        $repairStatusData   = $repairStatusRaw->pluck('total');

        // ----------------------------------------------------
        // STAFF PERFORMANCE (filtered by range)
        // ----------------------------------------------------
        $staffSalesQuery = DB::table('transactions')
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('users', 'transactions.staff_id', '=', 'users.id')
            ->where('transactions.type', 'Buy')
            ->groupBy('users.id', 'users.name')
            ->selectRaw('users.name, SUM(transaction_items.line_total) as total_sales')
            ->orderByDesc('total_sales')
            ->limit(6);

        $rangeFilter($staffSalesQuery, 'transactions.created_at');

        $staffSales       = $staffSalesQuery->get();
        $staffSalesLabels = $staffSales->pluck('name');
        $staffSalesData   = $staffSales->pluck('total_sales');

        // ----------------------------------------------------
        // FAVORITES & VIEWS (filtered by range)
        // ----------------------------------------------------
        $mostFavoritedQuery = DB::table('favorites')
            ->join('products', 'favorites.product_id', '=', 'products.id')
            ->selectRaw('products.name, COUNT(*) as total_favorites')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_favorites')
            ->limit(5);

        $rangeFilter($mostFavoritedQuery, 'favorites.created_at');

        $mostFavorited = $mostFavoritedQuery->get();

        $mostViewedQuery = DB::table('product_views')
            ->join('products', 'product_views.product_id', '=', 'products.id')
            ->selectRaw('products.name, COUNT(*) as views')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('views')
            ->limit(5);

        $rangeFilter($mostViewedQuery, 'product_views.created_at');

        $mostViewed = $mostViewedQuery->get();

        // ----------------------------------------------------
        // CONVERSION FUNNEL (views → favorites → orders)
        // ----------------------------------------------------
        $viewsCountQuery = DB::table('product_views');
        $rangeFilter($viewsCountQuery, 'created_at');
        $viewsCount = $viewsCountQuery->count();

        $favoritesCountQuery = DB::table('favorites');
        $rangeFilter($favoritesCountQuery, 'created_at');
        $favoritesCount = $favoritesCountQuery->count();

        $ordersCountQuery = DB::table('transactions')
            ->where('type', 'Buy');
        $rangeFilter($ordersCountQuery, 'created_at');
        $ordersCount = $ordersCountQuery->count();

        $funnelLabels = ['Views', 'Favorites', 'Orders'];
        $funnelData   = [
            $viewsCount,
            $favoritesCount,
            $ordersCount,
        ];

        // ----------------------------------------------------
        // QUICK ACTIONS
        // ----------------------------------------------------
        $quickActions = [
            [
                'label'       => 'Create New Product',
                'description' => 'Add a new jewelry item to your inventory.',
                'href'        => route('admin.products.create'),
            ],
            [
                'label'       => 'View All Products',
                'description' => 'Manage stock, prices, and visibility.',
                'href'        => route('admin.products.index'),
            ],
            [
                'label'       => 'View Pawn Items',
                'description' => 'Monitor active, redeemed, and forfeited pawn items.',
                'href'        => route('admin.pawn.index'),
            ],
            [
                'label'       => 'View Repairs',
                'description' => 'Check pending and completed repair jobs.',
                'href'        => route('admin.repairs.index'),
            ],
            [
                'label'       => 'View Transactions',
                'description' => 'Review all buy/sell transactions.',
                'href'        => route('admin.transactions.index'),
            ],
            [
                'label'       => 'View Customers',
                'description' => 'See your customer list and profiles.',
                'href'        => route('admin.customers.index'),
            ],
        ];

    $minSupport = 4; // adjust: minimum # of transactions for a combo
    $raw = $eclat->mine($minSupport);

    $itemsets = $raw['frequent_itemsets'] ?? [];

    // Only combos with at least 2 products (pairs, triplets, etc.)
    $itemsets = array_filter($itemsets, function ($set) {
        return isset($set['items']) && count($set['items']) >= 2;
    });

    // Sort by support desc (already sorted in Python, but just in case)
    usort($itemsets, function ($a, $b) {
        return ($b['support'] ?? 0) <=> ($a['support'] ?? 0);
    });

    // Take top 10 combos
    $itemsets = array_slice($itemsets, 0, 5);

    // Collect all product IDs used
        $allProductIds = collect($itemsets)
        ->pluck('items')
        ->flatten()
        ->unique()
        ->values()
        ->all();

        $productNames = Product::whereIn('id', $allProductIds)->pluck('name', 'id');

        $frequentComboLabels = [];
        $frequentComboSupport = [];

        foreach ($itemsets as $set) {
        $ids = $set['items'] ?? [];
        $support = $set['support'] ?? 0;

        // Join product names: "Ring A + Necklace B + ..."
        $label = collect($ids)->map(function ($id) use ($productNames) {
            return $productNames[$id] ?? "Product {$id}";
        })->implode(' , ');

        $frequentComboLabels[] = $label;
        $frequentComboSupport[] = $support;
    }

        return view('admin.analytics', [
            'range'      => $range,
            'rangeLabel' => $rangeLabel,

            // Top metric cards
            'totalRevenue'        => $totalRevenue,
            'todayRevenue'        => $todayRevenue,
            'weekRevenue'         => $weekRevenue,
            'monthRevenue'        => $monthRevenue,
            'avgOrderValue'       => $avgOrderValue,

            'todayRevenueChange'  => $todayRevenueChange,
            'weekRevenueChange'   => $weekRevenueChange,
            'monthRevenueChange'  => $monthRevenueChange,

            'todayRevenueData'    => $todayRevenueData,
            'weekRevenueData'     => $weekRevenueData,
            'monthRevenueData'    => $monthRevenueData,

            'totalOrders'         => $totalOrders ?? 0,
            'totalOrdersChange'   => $totalOrdersChange ?? 0,

            // Sales trend
            'salesByDayLabels' => $salesByDayLabels,
            'salesByDayData'   => $salesByDayData,

            // Products / inventory
            'totalProducts'           => $totalProducts,
            'publishedProducts'       => $publishedProducts,
            'lowStockCount'           => $lowStockCount,
            'materialLabels'          => $materialLabels,
            'materialData'            => $materialData,
            'revenueByCategoryLabels' => $revenueByCategoryLabels,
            'revenueByCategoryData'   => $revenueByCategoryData,
            'topProducts'             => $topProducts,

            // Customers
            'totalCustomers'        => $totalCustomers,
            'newCustomersThisMonth' => $newCustomersThisMonth,

            // Pawn / repair
            'pawnStatusLabels'  => $pawnStatusLabels,
            'pawnStatusData'    => $pawnStatusData,
            'repairStatusLabels'=> $repairStatusLabels,
            'repairStatusData'  => $repairStatusData,

            // Staff performance
            'staffSalesLabels'  => $staffSalesLabels,
            'staffSalesData'    => $staffSalesData,

            // Favorites / views
            'mostFavorited'     => $mostFavorited,
            'mostViewed'        => $mostViewed,

            // Funnel
            'funnelLabels'      => $funnelLabels,
            'funnelData'        => $funnelData,

            'quickActions'      => $quickActions,

            'frequentComboLabels'  => $frequentComboLabels,
            'frequentComboSupport' => $frequentComboSupport,
        ]);
    }
}
