<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $q    = $request->string('q')->trim()->toString();
        $date = $request->input('date');

        $transactions = Transaction::with(['items.product', 'customer', 'staff'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    if (ctype_digit($q)) {
                        $id = (int) ltrim($q, '0');
                        if ($id > 0) {
                            $sub->orWhere('id', $id);
                        }
                    }

                    $sub->orWhereHas('customer', function ($cq) use ($q) {
                        $cq->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                    });

                    $sub->orWhereHas('items.product', function ($pq) use ($q) {
                        $pq->where('name', 'like', "%{$q}%");
                    });
                });
            })
            ->when($date, function ($query) use ($date) {
                $query->whereDate('created_at', $date);
            })
            ->withSum('items as total_amount', 'line_total')
            ->latest()
            ->paginate(10)
            ->appends($request->only('q', 'date'));

        return view('admin.transactions.index', compact('transactions'));
    }

    public function create()
    {
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

        $customers = Customer::orderBy('name')
            ->get(['id', 'name', 'email', 'contact_no']);

        return view('admin.transactions.form', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $mode = $request->input('customer_mode');

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
            $rules['customer_name']  = ['required', 'string', 'max:255'];
            $rules['customer_email'] = ['nullable', 'email', 'max:255']; // keep nullable
            $rules['customer_phone'] = ['nullable', 'string', 'max:20'];
        }

        $validated   = $request->validate($rules);
        $transaction = null;

        try {
            DB::transaction(function () use ($validated, $mode, &$transaction) {
                if ($mode === 'existing') {
                    $customerId = $validated['customer_id'];
                } else {
                    $customer = Customer::create([
                        'name'       => $validated['customer_name'],
                        'email'      => $validated['customer_email'] ?? null,
                        'contact_no' => $validated['customer_phone'] ?? null,
                    ]);
                    $customerId = $customer->id;
                }

                $transaction = Transaction::create([
                    'customer_id' => $customerId,
                    'staff_id'    => auth()->id(),
                    'type'        => 'Buy',
                ]);

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

                    Product::where('id', $item['product_id'])->decrement('quantity', $qty);
                }
            });
        } catch (QueryException $e) {
            // MySQL duplicate key error code = 1062
            if (($e->errorInfo[1] ?? null) === 1062 && str_contains($e->getMessage(), 'customers_email_unique')) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'customer_email' => 'That email is already registered. Please choose "Existing Customer" or use a different email.',
                    ]);
            }

            throw $e; // rethrow other DB errors
        }

        return redirect()
            ->route('admin.transactions.index')
            ->with([
                'success'                 => 'Transaction created successfully.',
                'download_transaction_id' => $transaction->id,
            ]);
    }


    public function download(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'staff']);
        
        $pdf = Pdf::loadView('admin.transactions.receipt', [
            'transaction' => $transaction,
        ])->setPaper('a4', 'portrait');

        $fileName = 'receipt_' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT) . '_' . date('YmdHis') . '.pdf';

        return $pdf->download($fileName);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'staff']);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function receipt(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'staff']);
        return view('admin.transactions.receipt', compact('transaction'));
    }
}