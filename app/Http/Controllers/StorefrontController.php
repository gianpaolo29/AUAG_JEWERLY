<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;


class StorefrontController extends Controller
{   

public function home()
{
    return view('welcome');
}
}
