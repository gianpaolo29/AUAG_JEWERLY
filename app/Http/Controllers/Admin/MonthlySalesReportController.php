<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class MonthlySalesReportController extends Controller
{
    public function download(Request $request)
    {
        // expects: month=YYYY-MM (example: 2025-12)
        $month = $request->input('month', now()->format('Y-m'));

        [$year, $mon] = array_map('intval', explode('-', $month));

        $start = Carbon::create($year, $mon, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        // Summary totals (using your schema)
        $summary = DB::table('transactions as t')
            ->join('transaction_items as ti', 'ti.transaction_id', '=', 't.id')
            ->where('t.type', 'Buy')
            ->whereBetween('t.created_at', [$start, $end])
            ->selectRaw('
                COUNT(DISTINCT t.id) as total_orders,
                SUM(ti.quantity)      as total_qty,
                SUM(ti.line_total)    as gross_sales
            ')
            ->first();

        // Breakdown by product (top 50)
        $products = DB::table('transactions as t')
            ->join('transaction_items as ti', 'ti.transaction_id', '=', 't.id')
            ->leftJoin('products as p', 'p.id', '=', 'ti.product_id')
            ->where('t.type', 'Buy')
            ->whereNotNull('ti.product_id')
            ->whereBetween('t.created_at', [$start, $end])
            ->groupBy('ti.product_id', 'p.name')
            ->selectRaw('
                ti.product_id,
                COALESCE(p.name, CONCAT("Product #", ti.product_id)) as product_name,
                SUM(ti.quantity)   as qty_sold,
                SUM(ti.line_total) as total_sales
            ')
            ->orderByDesc('total_sales')
            ->limit(50)
            ->get();

        // Breakdown by staff (optional; uses staff_id in transactions)
        $staff = DB::table('transactions as t')
            ->join('transaction_items as ti', 'ti.transaction_id', '=', 't.id')
            ->leftJoin('users as u', 'u.id', '=', 't.staff_id')
            ->where('t.type', 'Buy')
            ->whereBetween('t.created_at', [$start, $end])
            ->groupBy('t.staff_id', 'u.name')
            ->selectRaw('
                COALESCE(u.name, CONCAT("Staff #", t.staff_id)) as staff_name,
                COUNT(DISTINCT t.id) as orders_count,
                SUM(ti.line_total)   as staff_sales
            ')
            ->orderByDesc('staff_sales')
            ->get();

        $generatedAt = now();
        $generatedBy = optional(auth()->user())->name ?? 'System';

        $pdf = Pdf::loadView('reports.monthly-sales', compact(
            'month', 'start', 'end', 'summary', 'products', 'staff', 'generatedAt', 'generatedBy'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("Monthly-Sales-{$start->format('Y-m')}.pdf");
    }
}
