<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $type = $request->string('type')->toString();   // Buy / Pawn / Repair
        $date = $request->date('date');                 // Y-m-d

        $transactions = Transaction::query()
            ->with([
                'customer:id,name,email',
                'staff:id,name,email',

                // Products (morphOne)
                'items.product.picture',

                // Pawn items (morphMany)
                'items.pawnItem.pictures',

                'items.repair.picture',
            ])
            ->withSum('items as total_amount', 'line_total')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('id', $q)
                        ->orWhereHas('customer', function ($q2) use ($q) {
                            $q2->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        })
                        ->orWhereHas('staff', function ($q2) use ($q) {
                            $q2->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        });
                });
            })
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($date, fn ($query) => $query->whereDate('created_at', $date))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.transactions.index', compact('transactions', 'q', 'type', 'date'));
    }

    /* ----------------------------------------------------
     | CREATE – Walk-in sale (no customer)
     ---------------------------------------------------- */
    public function create()
    {
        $transaction = new Transaction;
        $staff = auth()->user();

        $products = Product::query()
            ->where('status', true)          // active
            ->where('quantity', '>', 0)      // in stock
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float) $p->price,
                    'stock' => (int) $p->quantity,   // your stock column
                    'sku' => '',                   // you said no sku
                    'unit' => '',                   // no unit
                    'image_url' => $p->image_url,
                ];
            })
            ->values();

        return view('admin.transactions.form', compact('transaction', 'staff', 'products'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load([
            'items.product.picture',
            'customer',
            'staff',
        ]);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $transaction = Transaction::create([
            'customer_id' => null,          // walk-in
            'staff_id' => auth()->id(),
            'type' => 'Buy',
        ]);

        foreach ($validated['items'] as $item) {
            $qty = (int) $item['quantity'];
            $price = (float) $item['unit_price'];
            $line = $qty * $price;

            $transaction->items()->create([
                'product_id' => $item['product_id'],
                'pawn_item_id' => null,
                'repair_id' => null,
                'quantity' => $qty,
                'unit_price' => $price,
                'line_total' => $line,
            ]);

            // ↓ Optionally deduct quantity here
            Product::where('id', $item['product_id'])->decrement('quantity', $qty);
        }

        // Optional: If you want to auto-inactivate product when quantity hits 0,
        // you can run an extra update or handle it in Product model (see note below).

        return redirect()
            ->route('admin.transactions.index')
            ->with('success', 'Sale recorded successfully.');
    }

    /* ----------------------------------------------------
     | EDIT – still walk-in, just editing items
     ---------------------------------------------------- */
    public function edit(Transaction $transaction)
    {
        $transaction->load(['items.product', 'staff']);
        $staff = auth()->user();

        $products = Product::query()
            // status = active + quantity > 0
            ->where('status', true)          // if status is tinyint(1) / boolean
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                // Try to figure out image column gracefully
                $rawPath = $p->image_url
                    ?? $p->image
                    ?? $p->image_path
                    ?? null;

                if ($rawPath) {
                    $imageUrl = Str::startsWith($rawPath, ['http://', 'https://'])
                        ? $rawPath
                        : asset('storage/'.ltrim($rawPath, '/'));
                } else {
                    $imageUrl = asset('images/placeholder-product.png');
                }

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float) $p->price,
                    // map your DB quantity → stock for the JS
                    'stock' => (int) $p->quantity,
                    // you said you don't have sku/unit, so just send empty
                    'sku' => '',
                    'unit' => '',
                    'image_url' => $imageUrl,
                ];
            });

        return view('admin.transactions.form', compact('transaction', 'staff', 'products'));
    }

    /* ----------------------------------------------------
     | UPDATE – items only (still walk-in)
     ---------------------------------------------------- */
    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        // If you want to properly adjust stock on edit, you’d first need to
        // restore quantities from old items here before deleting them.

        $transaction->items()->delete();

        foreach ($validated['items'] as $item) {
            $qty = (int) $item['quantity'];
            $price = (float) $item['unit_price'];
            $line = $qty * $price;

            $transaction->items()->create([
                'product_id' => $item['product_id'],
                'pawn_item_id' => null,
                'repair_id' => null,
                'quantity' => $qty,
                'unit_price' => $price,
                'line_total' => $line,
            ]);

            // If you want to adjust stock on edit, you also need logic here
            // (depends how you want to handle stock corrections).
        }

        return redirect()
            ->route('admin.transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        // Optional: you could restore stock here if you want.
        $transaction->delete();

        return redirect()
            ->route('admin.transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }
}
