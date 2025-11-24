<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // --- DATE RANGE FILTER (for charts & funnel) ---
        // accepted: today | 7d | 30d | all
        $range = $request->input('range', '30d');

        $rangeFilter = function ($query, $column = 'created_at') use ($range) {
            if ($range === 'today') {
                $query->whereDate($column, today());
            } elseif ($range === '7d') {
                $query->where($column, '>=', now()->subDays(6));
            } elseif ($range === '30d') {
                $query->where($column, '>=', now()->subDays(29));
            }

            // 'all' = no filter
            return $query;
        };

        $rangeLabel = match ($range) {
            'today' => 'Today',
            '7d' => 'Last 7 Days',
            '30d' => 'Last 30 Days',
            default => 'All Time',
        };

        // --- SALES ANALYTICS (cards - all time) ---

        $totalRevenue = DB::table('transactions')
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.type', 'Buy')
            ->sum('transaction_items.line_total');

        $todayRevenue = DB::table('transactions')
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.type', 'Buy')
            ->whereDate('transactions.created_at', today())
            ->sum('transaction_items.line_total');

        $weekRevenue = DB::table('transactions')
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.type', 'Buy')
            ->where('transactions.created_at', '>=', now()->startOfWeek())
            ->sum('transaction_items.line_total');

        $monthRevenue = DB::table('transactions')
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.type', 'Buy')
            ->whereBetween('transactions.created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('transaction_items.line_total');

        $buyCount = DB::table('transactions')
            ->where('type', 'Buy')
            ->count();

        $avgOrderValue = $buyCount > 0
            ? round($totalRevenue / $buyCount, 2)
            : 0;

        // --- SALES TREND (filtered by range) ---

        $salesByDayQuery = DB::table('transactions')
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.type', 'Buy')
            ->selectRaw('DATE(transactions.created_at) as day, SUM(transaction_items.line_total) as total')
            ->groupBy('day')
            ->orderBy('day');

        $rangeFilter($salesByDayQuery, 'transactions.created_at');

        $salesByDay = $salesByDayQuery->get();

        $salesByDayLabels = $salesByDay->pluck('day')->map(fn ($d) => Carbon::parse($d)->format('M d'));
        $salesByDayData = $salesByDay->pluck('total');

        // --- PRODUCT / INVENTORY ANALYTICS ---

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
        $materialData = $materials->pluck('count');
        $topMaterial = $materials->sortByDesc('count')->first();

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

        $revenueByCategory = $revenueByCategoryQuery->get();

        $revenueByCategoryLabels = $revenueByCategory->pluck('category');
        $revenueByCategoryData = $revenueByCategory->pluck('total_sales');

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

        // --- CUSTOMER ANALYTICS (cards) ---

        $totalCustomers = DB::table('users')
            ->where('role', 'customer')
            ->count();

        $newCustomersThisMonth = DB::table('users')
            ->where('role', 'customer')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        // --- PAWN ANALYTICS (filtered by range) ---

        $pawnStatusQuery = DB::table('pawn_items')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status');

        $rangeFilter($pawnStatusQuery, 'created_at');

        $pawnStatusRaw = $pawnStatusQuery->get();

        $pawnStatusLabels = $pawnStatusRaw->pluck('status');
        $pawnStatusData = $pawnStatusRaw->pluck('total');

        // --- REPAIR ANALYTICS (filtered by range) ---

        $repairStatusQuery = DB::table('repairs')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status');

        $rangeFilter($repairStatusQuery, 'created_at');

        $repairStatusRaw = $repairStatusQuery->get();

        $repairStatusLabels = $repairStatusRaw->pluck('status');
        $repairStatusData = $repairStatusRaw->pluck('total');

        // --- STAFF PERFORMANCE (filtered by range) ---

        $staffSalesQuery = DB::table('transactions')
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->join('users', 'transactions.staff_id', '=', 'users.id')
            ->where('transactions.type', 'Buy')
            ->groupBy('users.id', 'users.name')
            ->selectRaw('users.name, SUM(transaction_items.line_total) as total_sales')
            ->orderByDesc('total_sales')
            ->limit(6);

        $rangeFilter($staffSalesQuery, 'transactions.created_at');

        $staffSales = $staffSalesQuery->get();

        $staffSalesLabels = $staffSales->pluck('name');
        $staffSalesData = $staffSales->pluck('total_sales');

        // --- FAVORITES & VIEWS (filtered by range) ---

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

        // --- CONVERSION FUNNEL (views → favorites → orders) ---

        $viewsCountQuery = DB::table('product_views');
        $rangeFilter($viewsCountQuery, 'created_at');
        $viewsCount = $viewsCountQuery->count();

        $favoritesCountQuery = DB::table('favorites');
        $rangeFilter($favoritesCountQuery, 'created_at');
        $favoritesCount = $favoritesCountQuery->count();

        $ordersCountQuery = DB::table('transactions')
            ->where('type', 'Buy');
        $rangeFilter($ordersCountQuery, 'created_at');
        $ordersCount = $ordersCountQuery->count(); // number of orders

        $funnelLabels = ['Views', 'Favorites', 'Orders'];
        $funnelData = [
            $viewsCount,
            $favoritesCount,
            $ordersCount,
        ];

        // --- SIMPLE "AI" SUGGESTIONS ---

        $aiSuggestions = [];

        if ($lowStockCount > 0) {
            $aiSuggestions[] = 'Some products are low on stock. Use AI to auto-flag items that might sell out soon based on past sales.';
        }

        if ($topMaterial) {
            $aiSuggestions[] = "Most of your catalog uses <strong>{$topMaterial->material}</strong>. Train recommendations to push similar-material items to interested customers.";
        }

        if ($avgOrderValue > 0 && $avgOrderValue < 2000) {
            $aiSuggestions[] = 'Average order value is ₱'.number_format($avgOrderValue, 2).'. Try AI-powered “bundle suggestions” or upsell at checkout.';
        }

        if ($monthRevenue > 0 && $todayRevenue == 0) {
            $aiSuggestions[] = 'No sales today yet. Use AI to identify your top customers and send them targeted offers.';
        }

        if ($mostFavorited->count() > 0) {
            $aiSuggestions[] = 'Your most favorited products are perfect candidates for remarketing ads and homepage highlights.';
        }

        if (empty($aiSuggestions)) {
            $aiSuggestions[] = 'Data looks stable. Next step: connect AI to build smart product recommendations and dynamic pricing tests.';
        }

        // --- QUICK ACTIONS (ADMIN SHORTCUTS) ---

        $quickActions = [
            [
                'label' => 'Create New Product',
                'description' => 'Add a new jewelry item to your inventory.',
                'href' => route('admin.products.create'),
            ],
            [
                'label' => 'View All Products',
                'description' => 'Manage stock, prices, and visibility.',
                'href' => route('admin.products.index'),
            ],
            [
                'label' => 'View Pawn Items',
                'description' => 'Monitor active, redeemed, and forfeited pawn items.',
                'href' => route('admin.pawn.index'),
            ],
            [
                'label' => 'View Repairs',
                'description' => 'Check pending and completed repair jobs.',
                'href' => route('admin.repairs.index'),
            ],
            [
                'label' => 'View Transactions',
                'description' => 'Review all buy/sell transactions.',
                'href' => route('admin.transactions.index'),
            ],
            [
                'label' => 'View Customers',
                'description' => 'See your customer list and profiles.',
                'href' => route('admin.customers.index'),
            ],
        ];

        return view('admin.analytics', [
            'range' => $range,
            'rangeLabel' => $rangeLabel,

            'totalRevenue' => $totalRevenue,
            'todayRevenue' => $todayRevenue,
            'weekRevenue' => $weekRevenue,
            'monthRevenue' => $monthRevenue,
            'avgOrderValue' => $avgOrderValue,
            'salesByDayLabels' => $salesByDayLabels,
            'salesByDayData' => $salesByDayData,

            'totalProducts' => $totalProducts,
            'publishedProducts' => $publishedProducts,
            'lowStockCount' => $lowStockCount,
            'materialLabels' => $materialLabels,
            'materialData' => $materialData,
            'revenueByCategoryLabels' => $revenueByCategoryLabels,
            'revenueByCategoryData' => $revenueByCategoryData,
            'topProducts' => $topProducts,

            'totalCustomers' => $totalCustomers,
            'newCustomersThisMonth' => $newCustomersThisMonth,

            'pawnStatusLabels' => $pawnStatusLabels,
            'pawnStatusData' => $pawnStatusData,
            'repairStatusLabels' => $repairStatusLabels,
            'repairStatusData' => $repairStatusData,

            'staffSalesLabels' => $staffSalesLabels,
            'staffSalesData' => $staffSalesData,

            'mostFavorited' => $mostFavorited,
            'mostViewed' => $mostViewed,

            'funnelLabels' => $funnelLabels,
            'funnelData' => $funnelData,

            'aiSuggestions' => $aiSuggestions,
            'quickActions' => $quickActions,
        ]);
    }
}
