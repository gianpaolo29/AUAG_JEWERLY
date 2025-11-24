<?php

namespace App\Http\Controllers\Staff;
use App\Http\Controllers\Controller;
use App\Models\PawnItem;
use App\Models\Repair;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class StaffDashboardController extends Controller
{
    /**
     * Staff Dashboard
     */
    public function index()
    {
    $staffId = auth()->id();

    return view('staff.dashboard', [

        // K
        'pawnHandled' => PawnItem::count(),
        'completedRepairs' => Repair::where('status', 'completed')->count(),
        'activeRepairs' => Repair::where('status', 'in_progress')->count(),
        'transactionsCount' => Transaction::where('staff_id', $staffId)->count(),

        // Recent items
        'recentPawnItems' => PawnItem::with('customer')
            ->latest()
            ->take(5)
            ->get(),

        'recentTransactions' => Transaction::with('staff')
            ->where('staff_id', $staffId)
            ->latest()
            ->take(5)
            ->get(),
    ]);
}
}