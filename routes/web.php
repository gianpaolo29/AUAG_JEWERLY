<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\PawnItemController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RepairController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Customer\ShopController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('auth/google', [GoogleController::class, 'redirect'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'callback']);

Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
    ->name('password.request');

Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('password.email');

Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    ->name('password.reset');

Route::post('reset-password', [NewPasswordController::class, 'store'])
    ->name('password.store');

Route::get('/', [StorefrontController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('products', [ProductController::class, 'store'])->name('products.store');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');

    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('customers', CustomerController::class)->parameters([
        'customers' => 'customer',
    ])->except(['show']);

    Route::resource('staff', StaffController::class)->parameters([
        'staff' => 'staff',
    ])->except(['show']);

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('transactions/{transaction}/show', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/pawns', [PawnItemController::class, 'index'])->name('pawn.index');
    Route::get('/pawns/create', [PawnItemController::class, 'create'])->name('pawn.create');
    Route::post('/pawns', [PawnItemController::class, 'store'])->name('pawn.store');
    Route::get('pawns/{pawnItem}/edit', [PawnItemController::class, 'edit'])->name('pawn.edit');
    Route::put('pawns/{pawnItem}', [PawnItemController::class, 'update'])->name('pawn.update');
    Route::post('pawns/{pawnItem}/redeem', [PawnItemController::class, 'redeem'])->name('pawn.redeem');

    Route::resource('repairs', RepairController::class)
        ->names([
            'index' => 'repairs.index',
            'create' => 'repairs.create',
            'store' => 'repairs.store',
            'edit' => 'repairs.edit',
            'update' => 'repairs.update',
            'destroy' => 'repairs.destroy',
        ])->parameters([
            'repairs' => 'repair',
        ]);

    Route::post('repairs/{repair}/complete', [RepairController::class, 'markComplete'])->name('repairs.complete');

});

Route::middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/staff/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
});

Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/customer/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
});

require __DIR__.'/auth.php';
