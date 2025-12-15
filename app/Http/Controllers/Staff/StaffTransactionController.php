<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class StaffTransactionController extends Controller
{
    /* ----------------------------------------------------
     | INDEX – Staff sees ONLY their own transactions
     ---------------------------------------------------- */
    public function index(Request $request)
    {
        $q    = $request->string('q')->trim()->toString(); // search term
        $date = $request->input('date');                  // YYYY-MM-DD

        $transactions = Transaction::with(['items.product', 'customer', 'staff'])
            ->where('staff_id', auth()->id()) // still only show this staff's transactions
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    // 1) Search by ID (numeric)
                    if (ctype_digit($q)) {
                        // allow searching by raw ID or zero-padded ID typed as "000123"
                        $id = (int) ltrim($q, '0');
                        if ($id > 0) {
                            $sub->orWhere('id', $id);
                        }
                    }

                    // 2) Search by customer name or email
                    $sub->orWhereHas('customer', function ($cq) use ($q) {
                        $cq->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                    });

                    // 3) Search by product name (items they bought)
                    $sub->orWhereHas('items.product', function ($pq) use ($q) {
                        $pq->where('name', 'like', "%{$q}%");
                    });
                });
            })
            ->when($date, function ($query) use ($date) {
                // Filter by transaction date
                $query->whereDate('created_at', $date);
            })
            ->withSum('items as total_amount', 'line_total')
            ->latest()
            ->paginate(10)
            ->appends($request->only('q', 'date')); // keep filters on pagination links

        return view('staff.transactions.index', compact('transactions'));
    }


    /* ----------------------------------------------------
     | CREATE – Sale with new or existing customer
     ---------------------------------------------------- */
    public function create()
    {
        // Products for the cart (Alpine)
        $products = Product::where('status', true)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id'        => $p->id,
                'name'      => $p->name,
                'price'     => (float) $p->price,
                'stock'     => (int) $p->quantity,
                'image_url' => $p->image_url,
            ]);

        // Customers for the "Existing Customer" tab
        $customers = Customer::orderBy('name')
            ->get(['id', 'name', 'email', 'contact_no']);

        return view('staff.transactions.form', compact('products', 'customers'));
    }

    /* ----------------------------------------------------
     | STORE – Save sale (new or existing customer)
     | THEN redirect to index, where PDF will auto-download
     ---------------------------------------------------- */
    public function store(Request $request)
    {
        $mode = $request->input('customer_mode'); // 'existing' or 'new'

        $rules = [
            'customer_mode'            => ['required', Rule::in(['existing', 'new'])],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.product_id'       => ['required', Rule::exists('products', 'id')],
            'items.*.quantity'         => ['required', 'integer', 'min:1'],
            'items.*.unit_price'       => ['required', 'numeric', 'min:0'],
        ];

        if ($mode === 'existing') {
            $rules['customer_id'] = ['required', 'integer', Rule::exists('customers', 'id')];
        }

        if ($mode === 'new') {
            $rules['customer_name']    = ['required', 'string', 'max:255'];
            $rules['customer_email']   = ['nullable', 'email', 'max:255'];
            $rules['customer_phone']   = ['nullable', 'string', 'max:20'];
            $rules['customer_address'] = ['nullable', 'string', 'max:255'];
        }

        $validated   = $request->validate($rules);
        $transaction = null;

        DB::transaction(function () use ($validated, $mode, &$transaction) {
            // Resolve customer_id
            if ($mode === 'existing') {
                $customerId = $validated['customer_id'];
            } else {
                $customer = Customer::create([
                    'name'       => $validated['customer_name'],
                    'email'      => $validated['customer_email'] ?? null,
                    'contact_no' => $validated['customer_phone'] ?? null,
                    'address'    => $validated['customer_address'] ?? null,
                ]);
                $customerId = $customer->id;
            }

            // Create transaction
            $transaction = Transaction::create([
                'customer_id' => $customerId,
                'staff_id'    => auth()->id(),
                'type'        => 'Buy',
            ]);

            // Items + stock deduction
            foreach ($validated['items'] as $item) {
                $qty   = (int) $item['quantity'];
                $price = (float) $item['unit_price'];

                $transaction->items()->create([
                    'product_id'   => $item['product_id'],
                    'quantity'     => $qty,
                    'unit_price'   => $price,
                    'line_total'   => $qty * $price,
                    'pawn_item_id' => null,
                    'repair_id'    => null,
                ]);

                Product::where('id', $item['product_id'])
                    ->decrement('quantity', $qty);
            }
        });

        // Redirect to index and tell it which transaction to download as PDF
        return redirect()
            ->route('staff.transactions.index')
            ->with([
                'success'                 => 'Transaction created successfully.',
                'download_transaction_id' => $transaction->id,
            ]);
    }

    /* ----------------------------------------------------
     | DOWNLOAD – Generate & download PDF
     | (Called by index via hidden link/script)
     ---------------------------------------------------- */
    public function download(Transaction $transaction)
    {
        abort_if($transaction->staff_id !== auth()->id(), 403);

        $transaction->load(['items.product', 'customer']);

        $pdf = Pdf::loadView('staff.transactions.receipt', [
                'transaction' => $transaction,
            ])
            ->setPaper('a4', 'portrait');

        $fileName = 'transaction_' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($fileName);
    }

    /* ----------------------------------------------------
     | SHOW – View staff transaction (normal page)
     ---------------------------------------------------- */
    public function show(Transaction $transaction)
    {
        abort_if($transaction->staff_id !== auth()->id(), 403);

        $transaction->load(['items.product', 'customer']);

        return view('staff.transactions.show', compact('transaction'));
    }

    /* ----------------------------------------------------
     | RECEIPT – HTML receipt (if you still need web view)
     ---------------------------------------------------- */
    public function receipt(Transaction $transaction)
    {
        abort_if($transaction->staff_id !== auth()->id(), 403);

        $transaction->load(['items.product', 'customer']);

        return view('staff.transactions.receipt', [
            'transaction' => $transaction,
        ]);
    }
}
