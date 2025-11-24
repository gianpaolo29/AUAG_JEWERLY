<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class StorefrontController extends Controller
{
    public function index()
    {
        // Subquery: total quantity sold per product
        $salesSub = DB::table('transaction_items')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id');

        // Join products with total_sold, order by best sellers
        $bestSellers = Product::query()
            ->with('picture')
            ->leftJoinSub($salesSub, 'sales', function ($join) {
                $join->on('products.id', '=', 'sales.product_id');
            })
            ->select('products.*', DB::raw('COALESCE(sales.total_sold, 0) as total_sold'))
            ->orderByDesc('total_sold')
            ->limit(8)
            ->get();

        // If no product has sales (all total_sold = 0), fallback to random 8 products
        $hasSales = $bestSellers->where('total_sold', '>', 0)->isNotEmpty();

        if (! $hasSales) {
            $bestSellers = Product::query()
                ->with('picture')
                ->inRandomOrder()
                ->limit(8)
                ->get();
        }

        return view('welcome', [
            'bestSellers' => $bestSellers,
        ]);
    }
}
