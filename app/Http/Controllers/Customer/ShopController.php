<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->active();

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('material', 'LIKE', "%{$search}%")
                    ->orWhere('style', 'LIKE', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
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
            $query->whereIn('category_id', (array) $request->category);
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
                $query->orderBy('view_count', 'desc');
                break;
            case 'recommended':
                if (auth()->check()) {
                    $user = auth()->user();
                    $query
                        // 1. Prioritize favorited products
                        ->leftJoin('favorites as f', function ($join) use ($user) {
                            $join->on('f.product_id', '=', 'products.id')
                                ->where('f.user_id', $user->id);
                        })

                        // 2. Count user-specific views
                        ->leftJoin('product_views as pv', function ($join) use ($user) {
                            $join->on('pv.product_id', '=', 'products.id')
                                ->where('pv.user_id', $user->id);
                        })
                        ->select('products.*')
                        ->selectRaw('COUNT(f.product_id) as user_favorited')
                        ->selectRaw('COUNT(pv.product_id) as user_view_count')

                        ->groupBy('products.id')

                        // ORDER PRIORITY:
                        ->orderByDesc('user_favorited')   // favorited first
                        ->orderByDesc('user_view_count')  // then most viewed by user
                        ->orderByDesc('view_count')       // then most viewed overall
                        ->orderByDesc('products.created_at'); // fallback newest
                } else {
                    // If no user logged in → just use overall views + newest
                    $query->orderBy('view_count', 'desc')
                        ->orderBy('created_at', 'desc');
                }
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

        if (Auth::check()) {
            ProductView::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'views' => $product->view_count,
        ]);
    }
}
