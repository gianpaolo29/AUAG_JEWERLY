<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffTransactionController extends Controller
{
    /* ----------------------------------------------------
     | INDEX – Staff sees ONLY their own transactions
     ---------------------------------------------------- */
    public function index()
    {
        $transactions = Transaction::with(['items.product'])
            ->where('staff_id', auth()->id())
            ->withSum('items as total_amount', 'line_total')
            ->latest()
            ->paginate(10);

        return view('staff.transactions.index', compact('transactions'));
    }

    /* ----------------------------------------------------
     | CREATE – Walk-in sale
     ---------------------------------------------------- */
    public function create()
    {
        $products = Product::where('status', true)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'stock' => (int) $p->quantity,
                'image_url' => $p->image_url,
            ]);

        return view('staff.transactions.form', compact('products'));
    }

    /* ----------------------------------------------------
     | STORE – Save a walk-in sale
     ---------------------------------------------------- */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $transaction = Transaction::create([
            'customer_id' => null,
            'staff_id' => auth()->id(),
            'type' => 'Buy',
        ]);

        foreach ($validated['items'] as $item) {
            $qty = (int) $item['quantity'];
            $price = (float) $item['unit_price'];

            $transaction->items()->create([
                'product_id'  => $item['product_id'],
                'quantity'    => $qty,
                'unit_price'  => $price,
                'line_total'  => $qty * $price,
                'pawn_item_id' => null,
                'repair_id'    => null,
            ]);

            // stock deduction
            Product::where('id', $item['product_id'])
                ->decrement('quantity', $qty);
        }

        return redirect()
            ->route('staff.transactions.form')
            ->with('success', 'Sale recorded successfully.');
    }

    /* ----------------------------------------------------
     | SHOW – View staff transaction
     ---------------------------------------------------- */
    public function show(Transaction $transaction)
    {
        abort_if($transaction->staff_id !== auth()->id(), 403);

        $transaction->load(['items.product']);

        return view('staff.transactions.show', compact('transaction'));
    }
}
