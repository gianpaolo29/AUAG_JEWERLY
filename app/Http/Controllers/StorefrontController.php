<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;

class StorefrontController extends Controller
{
    public function index()
    {

        $hasSales = TransactionItem::exists();

        $bestSellers = $hasSales ?
            Product::withSum('transactionItems as total_sales', 'line_total')
                ->with('picture')
                ->orderByDesc('total_sales')
                ->limit(8)
                ->get() :
            Product::inRandomOrder()->with('picture')->limit(8)->get();

        return view('welcome', [
            'bestSellers' => $bestSellers,
        ]);
    }
}
