<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\EclatService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request, EclatService $eclat)
    {
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

        $percentChange = function (float $current, float $previous): float {
            if ($previous > 0) {
                return round((($current - $previous) / $previous) * 100, 1);
            }
            return $current > 0 ? 100.0 : 0.0;
        };

        // Buy revenue base query
        $revenueBaseQuery = function () {
            return DB::table('transactions')
                ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
                ->where('transactions.type', 'Buy');
        };

        // ----------------------------------------------------
        // RANGE WINDOW (current vs previous) + spark mode
        // ----------------------------------------------------
        switch ($range) {
            case 'today':
                $rangeStart = now()->startOfDay();
                $rangeEnd   = now()->endOfDay();
                $prevStart  = now()->subDay()->startOfDay();
                $prevEnd    = now()->subDay()->endOfDay();
                $sparkMode  = 'hour';
                $sparkCount = 24;
                break;

            case '7d':
                $rangeStart = now()->subDays(6)->startOfDay();
                $rangeEnd   = now()->endOfDay();
                $prevStart  = now()->subDays(13)->startOfDay();
                $prevEnd    = now()->subDays(7)->endOfDay();
                $sparkMode  = 'day';
                $sparkCount = 7;
                break;

            case '30d':
                $rangeStart = now()->subDays(29)->startOfDay();
                $rangeEnd   = now()->endOfDay();
                $prevStart  = now()->subDays(59)->startOfDay();
                $prevEnd    = now()->subDays(30)->endOfDay();
                $sparkMode  = 'day';
                $sparkCount = 30;
                break;

            case 'all':
            default:
                $rangeStart = null;
                $rangeEnd   = null;
                $prevStart  = null;
                $prevEnd    = null;
                $sparkMode  = 'month';
                $sparkCount = 12;
                break;
        }

        $applyBetween = function ($query, string $column, $start, $end) {
            if ($start && $end) {
                $query->whereBetween($column, [$start, $end]);
            }
            return $query;
        };

        // ----------------------------------------------------
        // TOP CARDS (ALL FILTERED BY RANGE)
        // Revenue, Orders, AOV + sparklines + % change
        // Plus Items Sold, Unique Buyers
        // ----------------------------------------------------

        // Revenue (range)
        $rangeRevenueQ = $revenueBaseQuery();
        $applyBetween($rangeRevenueQ, 'transactions.created_at', $rangeStart, $rangeEnd);
        $rangeRevenue = (float) $rangeRevenueQ->sum('transaction_items.line_total');

        // Revenue (prev)
        $prevRevenue = 0.0;
        if ($prevStart && $prevEnd) {
            $prevRevenueQ = $revenueBaseQuery();
            $applyBetween($prevRevenueQ, 'transactions.created_at', $prevStart, $prevEnd);
            $prevRevenue = (float) $prevRevenueQ->sum('transaction_items.line_total');
        }

        $rangeRevenueChange = ($prevStart && $prevEnd)
            ? $percentChange($rangeRevenue, $prevRevenue)
            : 0.0;

        // Orders (range)
        $rangeOrdersQ = DB::table('transactions')->where('type', 'Buy');
        $applyBetween($rangeOrdersQ, 'created_at', $rangeStart, $rangeEnd);
        $rangeOrders = (int) $rangeOrdersQ->count();

        // Orders (prev)
        $prevOrders = 0;
        if ($prevStart && $prevEnd) {
            $prevOrdersQ = DB::table('transactions')->where('type', 'Buy');
            $applyBetween($prevOrdersQ, 'created_at', $prevStart, $prevEnd);
            $prevOrders = (int) $prevOrdersQ->count();
        }

        $rangeOrdersChange = ($prevStart && $prevEnd)
            ? $percentChange($rangeOrders, $prevOrders)
            : 0.0;

        // AOV (range)
        $rangeAov = $rangeOrders > 0 ? round($rangeRevenue / $rangeOrders, 2) : 0.0;
        $prevAov  = $prevOrders > 0 ? round($prevRevenue / $prevOrders, 2) : 0.0;

        $rangeAovChange = ($prevStart && $prevEnd)
            ? $percentChange($rangeAov, $prevAov)
            : 0.0;

        // Items Sold (range)
        $itemsSoldQ = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.type', 'Buy');
        $applyBetween($itemsSoldQ, 'transactions.created_at', $rangeStart, $rangeEnd);
        $itemsSold = (int) $itemsSoldQ->sum('transaction_items.quantity');

        // Items Sold (prev)
        $prevItemsSold = 0;
        if ($prevStart && $prevEnd) {
            $prevItemsSoldQ = DB::table('transaction_items')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.type', 'Buy');
            $applyBetween($prevItemsSoldQ, 'transactions.created_at', $prevStart, $prevEnd);
            $prevItemsSold = (int) $prevItemsSoldQ->sum('transaction_items.quantity');
        }

        $itemsSoldChange = ($prevStart && $prevEnd)
            ? $percentChange($itemsSold, $prevItemsSold)
            : 0.0;

        // Unique Buyers (range) - requires transactions.customer_id
        $uniqueBuyersQ = DB::table('transactions')
            ->where('type', 'Buy')
            ->whereNotNull('customer_id');
        $applyBetween($uniqueBuyersQ, 'created_at', $rangeStart, $rangeEnd);
        $uniqueBuyers = (int) $uniqueBuyersQ->distinct()->count('customer_id');

        // Unique Buyers (prev)
        $prevUniqueBuyers = 0;
        if ($prevStart && $prevEnd) {
            $prevUniqueBuyersQ = DB::table('transactions')
                ->where('type', 'Buy')
                ->whereNotNull('customer_id');
            $applyBetween($prevUniqueBuyersQ, 'created_at', $prevStart, $prevEnd);
            $prevUniqueBuyers = (int) $prevUniqueBuyersQ->distinct()->count('customer_id');
        }

        $uniqueBuyersChange = ($prevStart && $prevEnd)
            ? $percentChange($uniqueBuyers, $prevUniqueBuyers)
            : 0.0;

        // ----------------------------------------------------
        // SPARKLINES (Revenue / Orders / AOV)
        // - today => hourly
        // - 7d/30d => daily
        // - all => monthly last 12 months
        // ----------------------------------------------------
        $rangeRevenueData = [];
        $rangeOrdersData  = [];
        $rangeAovData     = [];

        if ($sparkMode === 'hour') {
            $rowsRev = $revenueBaseQuery()
                ->whereBetween('transactions.created_at', [$rangeStart, $rangeEnd])
                ->selectRaw('HOUR(transactions.created_at) as k, SUM(transaction_items.line_total) as total')
                ->groupBy('k')
                ->pluck('total', 'k');

            $rowsOrd = DB::table('transactions')
                ->where('type', 'Buy')
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                ->selectRaw('HOUR(created_at) as k, COUNT(*) as total')
                ->groupBy('k')
                ->pluck('total', 'k');

            for ($i = 0; $i < 24; $i++) {
                $r = (float) ($rowsRev[$i] ?? 0);
                $o = (int) ($rowsOrd[$i] ?? 0);

                $rangeRevenueData[] = $r;
                $rangeOrdersData[]  = $o;
                $rangeAovData[]     = $o > 0 ? round($r / $o, 2) : 0;
            }
        } elseif ($sparkMode === 'day') {
            $rowsRevQ = $revenueBaseQuery();
            $applyBetween($rowsRevQ, 'transactions.created_at', $rangeStart, $rangeEnd);
            $rowsRev = $rowsRevQ
                ->selectRaw('DATE(transactions.created_at) as k, SUM(transaction_items.line_total) as total')
                ->groupBy('k')
                ->pluck('total', 'k');

            $rowsOrdQ = DB::table('transactions')->where('type', 'Buy');
            $applyBetween($rowsOrdQ, 'created_at', $rangeStart, $rangeEnd);
            $rowsOrd = $rowsOrdQ
                ->selectRaw('DATE(created_at) as k, COUNT(*) as total')
                ->groupBy('k')
                ->pluck('total', 'k');

            for ($i = 0; $i < $sparkCount; $i++) {
                $day = $rangeStart->copy()->addDays($i)->toDateString();
                $r = (float) ($rowsRev[$day] ?? 0);
                $o = (int) ($rowsOrd[$day] ?? 0);

                $rangeRevenueData[] = $r;
                $rangeOrdersData[]  = $o;
                $rangeAovData[]     = $o > 0 ? round($r / $o, 2) : 0;
            }
        } else {
            $sparkStart = now()->subMonths(11)->startOfMonth();
            $sparkEnd   = now()->endOfMonth();

            $rowsRev = $revenueBaseQuery()
                ->whereBetween('transactions.created_at', [$sparkStart, $sparkEnd])
                ->selectRaw('DATE_FORMAT(transactions.created_at, "%Y-%m") as k, SUM(transaction_items.line_total) as total')
                ->groupBy('k')
                ->pluck('total', 'k');

            $rowsOrd = DB::table('transactions')
                ->where('type', 'Buy')
                ->whereBetween('created_at', [$sparkStart, $sparkEnd])
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as k, COUNT(*) as total')
                ->groupBy('k')
                ->pluck('total', 'k');

            for ($i = 0; $i < 12; $i++) {
                $k = $sparkStart->copy()->addMonths($i)->format('Y-m');

                $r = (float) ($rowsRev[$k] ?? 0);
                $o = (int) ($rowsOrd[$k] ?? 0);

                $rangeRevenueData[] = $r;
                $rangeOrdersData[]  = $o;
                $rangeAovData[]     = $o > 0 ? round($r / $o, 2) : 0;
            }
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
        $salesByDayLabels = $salesByDay->pluck('day')->map(fn ($d) => Carbon::parse($d)->format('M d'));
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
        // CUSTOMER ANALYTICS (Total = all-time, New = range-based)
        // ----------------------------------------------------
        $totalCustomers = DB::table('customers')->count();

        $newCustomersInRangeQ = DB::table('customers');
        $applyBetween($newCustomersInRangeQ, 'created_at', $rangeStart, $rangeEnd);
        $newCustomersInRange = (int) $newCustomersInRangeQ->count();

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

        $repairStatusRaw     = $repairStatusQuery->get();
        $repairStatusLabels  = $repairStatusRaw->pluck('status');
        $repairStatusData    = $repairStatusRaw->pluck('total');

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

        // ----------------------------------------------------
        // Frequently Bought Together (kept ALL TIME)
        // ----------------------------------------------------
        $minSupport = 5;
        $raw = $eclat->mine($minSupport);

        $itemsets = $raw['frequent_itemsets'] ?? [];

        $itemsets = array_filter($itemsets, function ($set) {
            return isset($set['items']) && count($set['items']) >= 2;
        });

        usort($itemsets, function ($a, $b) {
            return ($b['support'] ?? 0) <=> ($a['support'] ?? 0);
        });

        $itemsets = array_slice($itemsets, 0, 5);

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

            $label = collect($ids)->map(function ($id) use ($productNames) {
                return $productNames[$id] ?? "Product {$id}";
            })->implode(' , ');

            $frequentComboLabels[] = $label;
            $frequentComboSupport[] = $support;
        }

        return view('admin.analytics', [
            'range'      => $range,
            'rangeLabel' => $rangeLabel,

            // Top cards (range-based)
            'rangeRevenue'        => $rangeRevenue,
            'rangeRevenueChange'  => $rangeRevenueChange,
            'rangeRevenueData'    => $rangeRevenueData,

            'rangeOrders'         => $rangeOrders,
            'rangeOrdersChange'   => $rangeOrdersChange,
            'rangeOrdersData'     => $rangeOrdersData,

            'rangeAov'            => $rangeAov,
            'rangeAovChange'      => $rangeAovChange,
            'rangeAovData'        => $rangeAovData,

            'itemsSold'           => $itemsSold,
            'itemsSoldChange'     => $itemsSoldChange,

            'uniqueBuyers'        => $uniqueBuyers,
            'uniqueBuyersChange'  => $uniqueBuyersChange,

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
            'totalCustomers'     => $totalCustomers,
            'newCustomersInRange'=> $newCustomersInRange,

            // Pawn / repair
            'pawnStatusLabels'   => $pawnStatusLabels,
            'pawnStatusData'     => $pawnStatusData,
            'repairStatusLabels' => $repairStatusLabels,
            'repairStatusData'   => $repairStatusData,

            // Staff performance
            'staffSalesLabels'   => $staffSalesLabels,
            'staffSalesData'     => $staffSalesData,

            // Favorites / views
            'mostFavorited'      => $mostFavorited,
            'mostViewed'         => $mostViewed,

            'quickActions'       => $quickActions,

            // Frequent combos
            'minSupport'            => $minSupport,
            'frequentComboLabels'   => $frequentComboLabels,
            'frequentComboSupport'  => $frequentComboSupport,
        ]);
    }
}
