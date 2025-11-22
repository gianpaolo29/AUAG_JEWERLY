<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        // Load products + image + category
        $q = Product::query()->with(['picture', 'category']);

        // ---------- Category filter ----------
        $catIds = collect((array) $request->input('category'))
            ->filter(fn ($v) => is_numeric($v))
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();

        if (! empty($catIds)) {
            $q->whereIn('category_id', $catIds);
        }

        // ---------- Price order (from radio) ----------
        $po = $request->input('price_order');

        if ($po === 'asc' || $po === 'desc') {
            $q->orderBy('price', $po);
        } else {
            // ---------- Sort dropdown (fallback) ----------
            $sort = $request->input('sort');

            if ($sort === 'newest') {
                $q->latest(); // created_at desc
            } else {
                $q->orderBy('name'); // default A–Z
            }
        }

        // ---------- Pagination ----------
        $products = $q->paginate(12)->appends($request->query());

        // ---------- Active filter counter ----------
        $activeFilterCount = (filled($po) ? 1 : 0) + count($catIds);

        // ---------- Categories for filter UI ----------
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('customer.shop.index', compact('products', 'categories', 'activeFilterCount'));
    }
}
