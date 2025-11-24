<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductView;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->active();
        
        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('material', 'LIKE', "%{$search}%")
                  ->orWhere('style', 'LIKE', "%{$search}%")
                  ->orWhereHas('category', function($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Price filter
        if ($request->has('min_price') && $request->min_price != '') {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price') && $request->max_price != '') {
            $query->where('price', '<=', $request->max_price);
        }

        // Category filter
        if ($request->has('category')) {
            $query->whereIn('category_id', (array)$request->category);
        }

        // Sorting
        switch ($request->get('sort', 'newest')) {
            case 'price-low':
                $query->orderBy('price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('views', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(12);
        $categories = Category::all();

        // Get user's favorite product IDs if logged in
        $favoriteIds = [];
        if (Auth::check()) {
            $favoriteIds = Auth::user()->favorites()->pluck('product_id')->toArray();
        }

        return view('customer.shop.index', compact('products', 'categories', 'favoriteIds'));
    }

    public function trackView(Product $product)
    {
        $product->increment('view_count');

        return response()->json([
            'success' => true,
            'views'   => $product->view_count,
        ]);
    }
}
