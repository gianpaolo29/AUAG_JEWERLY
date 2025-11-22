<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $q = Product::query()->with(['primaryPicture', 'pictures', 'category']);

        /* ---------v  --- Category filter (IDs) ------------ */
        // Expecting checkboxes like: <input type="checkbox" name="category[]" value="{{ $cat->id }}">
        $catIds = collect((array) $request->input('category'))
            ->filter(fn($v) => is_numeric($v))
            ->map(fn($v) => (int) $v)
            ->values()
            ->all();

        if (!empty($catIds)) {
            // If products table has category_id column:
            $q->whereIn('category_id', $catIds);

            // Or if you prefer relation-based filtering:
            // $q->whereHas('category', fn($cq) => $cq->whereIn('id', $catIds));
        }

        /* ------------ Price order (radio) ------------ */
        $po = $request->input('price_order'); // 'asc' | 'desc' | null
        if ($po === 'asc' || $po === 'desc') {
            $q->orderBy('price', $po);
        }

        /* ------------ Sort dropdown (fallback if no price order) ------------ */
        if (!in_array($po, ['asc','desc'], true)) {
            $sort = $request->input('sort');
            match ($sort) {
                'popular' => $q->orderByDesc('popularity'), // ensure this column exists or change it
                'newest'  => $q->latest(),                   // orders by created_at desc
                default   => $q->orderBy('name'),
            };
        }

        $products = $q->paginate(12)->appends($request->query());

        /* ------------ Active filter counter ------------ */
        $activeFilterCount = (filled($po) ? 1 : 0) + count($catIds);

        /* ------------ Pass categories for the UI ------------ */
        $categories = Category::orderBy('name')->get(['id','name']);

        return view('customer.shop.index', compact('products', 'categories', 'activeFilterCount'));
    }
}
