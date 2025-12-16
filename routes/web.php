<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AnalyticsController;
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
use App\Http\Controllers\Customer\FavoriteController;
use App\Http\Controllers\Customer\ShopController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffProfileController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Staff\StaffPawnController;
use App\Http\Controllers\Staff\StaffProductController;
use App\Http\Controllers\Staff\StaffRepairController;
use App\Http\Controllers\Staff\StaffTransactionController;
use App\Http\Controllers\StorefrontController;
use App\Services\Gold\GoldSummaryService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoldSpotPriceController;




    Route::get('/api/gold/spot', GoldSpotPriceController::class)->name('gold.spot');
    Route::get('/gold', function (GoldSummaryService $goldSummary)
    {
        return view('gold-index', [
            'gold' => $goldSummary->get(60),
        ]);
    })->name('gold.index');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');

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
    Route::get('/staff/profile', [StaffProfileController::class, 'edit'])->name('staff.profile.edit');
    Route::patch('/staff/profile', [StaffProfileController::class, 'update'])->name('staff.profile.update');
    Route::delete('/staff/profile', [StaffProfileController::class, 'destroy'])->name('staff.profile.destroy');


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
    Route::get('/transactions/{transaction}/download', [TransactionController::class, 'download'])->name('transactions.download');
    Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');

    Route::get('/pawns', [PawnItemController::class, 'index'])->name('pawn.index');
    Route::get('pawn/create', [PawnItemController::class, 'create'])->name('pawn.create');
    Route::post('pawn', [PawnItemController::class, 'store'])->name('pawn.store');
    Route::get('pawn/{pawnItem}/edit', [PawnItemController::class, 'edit'])->name('pawn.edit');
    Route::put('pawn/{pawnItem}', [PawnItemController::class, 'update'])->name('pawn.update');
    Route::delete('pawn/{pawnItem}', [PawnItemController::class, 'destroy'])->name('pawn.destroy');

    Route::post('pawn/{pawnItem}/redeem', [PawnItemController::class, 'redeem'])->name('pawn.redeem');
    Route::get('pawn/{pawnItem}/download', [PawnItemController::class, 'download'])->name('pawn.download');



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

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/forecast', [\App\Http\Controllers\GoldController::class, 'index'])->name('forecast');
    Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])->name('admin.notifications.markAllRead');

});

Route::middleware(['auth', 'role:customer'])->group(function () {

});

Route::middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/staff/dashboard', [StaffDashboardController::class, 'index'])->name('staff.dashboard');
    Route::get('/staff/products', [StaffProductController::class, 'index'])->name('staff.products.index');
    Route::get('/staff/transactions', [StaffTransactionController::class, 'index'])->name('staff.transactions.index');
    Route::get('/staff/transactions/create', [StaffTransactionController::class, 'create'])->name('staff.transactions.create');
    Route::post('/staff/transactions', [StaffTransactionController::class, 'store'])->name('staff.transactions.store');
    Route::get('/staff/transactions/{transaction}', [StaffTransactionController::class, 'show'])->name('staff.transactions.show');
    Route::get('/staff/transactions/{transaction}/receipt', [StaffTransactionController::class, 'receipt'])->name('staff.transactions.receipt');
    Route::get('/staff/transactions/{transaction}/download',[StaffTransactionController::class, 'download'])->name('staff.transactions.download');
    Route::get('/pawn/{pawn}/receipt', [StaffPawnController::class, 'receipt'])->name('staff.pawn.receipt');


    Route::get('/staff/pawns', [StaffPawnController::class, 'index'])->name('staff.pawn.index');
    Route::get('/pawn/create', [StaffPawnController::class, 'create'])->name('staff.pawn.create');
    Route::post('/pawn', [StaffPawnController::class, 'store'])->name('staff.pawn.store');
     Route::get('/pawn/{pawn}/download', [StaffPawnController::class, 'download'])->name('staff.pawn.download');
    Route::post('/pawn/{pawn}/redeem', [StaffPawnController::class, 'redeem'])->name('staff.pawn.redeem');

    // Repairs
    Route::get('/staff/repairs', [StaffRepairController::class, 'index'])->name('staff.repairs.index');
    Route::get('/staff/repairs/create', [StaffRepairController::class, 'create'])->name('staff.repairs.create');
    Route::post('/staff/repairs', [StaffRepairController::class, 'store'])->name('staff.repairs.store');
    Route::get('/staff/repairs/{repair}/edit', [StaffRepairController::class, 'edit'])->name('staff.repairs.edit');
    Route::put('/staff/repairs/{repair}', [StaffRepairController::class, 'update'])->name('staff.repairs.update');
    Route::delete('/staff/repairs/{repair}', [StaffRepairController::class, 'destroy'])->name('staff.repairs.destroy');
    Route::post('/staff/repairs/{repair}/complete', [StaffRepairController::class, 'markComplete'])
        ->name('staff.repairs.complete');
     Route::get('/staff/repairs/{repair}/download', [StaffRepairController::class, 'download'])->name('staff.repairs.download');

});

Route::post('/gold/sync', [\App\Http\Controllers\GoldController::class, 'sync'])->name('gold.sync');
Route::post('/gold/train', [\App\Http\Controllers\GoldController::class, 'train'])->name('gold.train');
Route::post('/gold/forecast', [\App\Http\Controllers\GoldController::class, 'forecast'])->name('gold.forecast');

require __DIR__.'/auth.php';
