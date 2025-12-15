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
    private const TYPE_BUY = 'Buy';

    /* ----------------------------------------------------
     | INDEX – Staff sees ONLY their own BUY transactions
     ---------------------------------------------------- */
    public function index(Request $request)
    {
        $q    = $request->string('q')->trim()->toString();
        $date = $request->input('date'); // YYYY-MM-DD

        $transactions = Transaction::with(['items.product', 'customer', 'staff'])
            ->where('staff_id', auth()->id())
            ->where('type', self::TYPE_BUY)
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    // Search by ID (supports "000123")
                    if (ctype_digit($q)) {
                        $id = (int) ltrim($q, '0');
                        if ($id > 0) {
                            $sub->orWhere('id', $id);
                        }
                    }

                    // Search by customer
                    $sub->orWhereHas('customer', function ($cq) use ($q) {
                        $cq->where('name', 'like', "%{$q}%")
                           ->orWhere('email', 'like', "%{$q}%");
                    });

                    // Search by product name
                    $sub->orWhereHas('items.product', function ($pq) use ($q) {
                        $pq->where('name', 'like', "%{$q}%");
                    });
                });
            })
            ->when($date, fn ($query) => $query->whereDate('created_at', $date))
            ->withSum('items as total_amount', 'line_total')
            ->latest()
            ->paginate(10)
            ->appends($request->only('q', 'date'));

        return view('staff.transactions.index', compact('transactions'));
    }

    /* ----------------------------------------------------
     | CREATE – BUY (new or existing customer)
     ---------------------------------------------------- */
    public function create()
    {
        $products = Product::query()
            ->where('status', true)
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

        return view('staff.transactions.form', compact('products', 'customers'));
    }

    /* ----------------------------------------------------
     | STORE – Save BUY
     ---------------------------------------------------- */
    public function store(Request $request)
    {
        $mode = $request->input('customer_mode'); // 'existing' | 'new'

        $rules = [
            'customer_mode'      => ['required', Rule::in(['existing', 'new'])],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('products', 'id')],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];

        if ($mode === 'existing') {
            $rules['customer_id'] = ['required', 'integer', Rule::exists('customers', 'id')];
        } else {
            $rules['customer_name']    = ['required', 'string', 'max:255'];
            $rules['customer_email']   = ['nullable', 'email', 'max:255'];
            $rules['customer_phone']   = ['nullable', 'string', 'max:20'];
            $rules['customer_address'] = ['nullable', 'string', 'max:255'];
        }

        $validated   = $request->validate($rules);
        $transaction = null;

        DB::transaction(function () use ($validated, $mode, &$transaction) {
            // Resolve customer
            if ($mode === 'existing') {
                $customerId = (int) $validated['customer_id'];
            } else {
                $customer = Customer::create([
                    'name'       => $validated['customer_name'],
                    'email'      => $validated['customer_email'] ?? null,
                    'contact_no' => $validated['customer_phone'] ?? null,
                    'address'    => $validated['customer_address'] ?? null,
                ]);
                $customerId = $customer->id;
            }

            // Create BUY transaction
            $transaction = Transaction::create([
                'customer_id' => $customerId,
                'staff_id'    => auth()->id(),
                'type'        => self::TYPE_BUY,
            ]);

            // Items + stock deduction (with basic stock protection)
            foreach ($validated['items'] as $item) {
                $productId = (int) $item['product_id'];
                $qty       = (int) $item['quantity'];
                $price     = (float) $item['unit_price'];

                $product = Product::lockForUpdate()->findOrFail($productId);

                if ($product->quantity < $qty) {
                    abort(422, "Not enough stock for {$product->name}.");
                }

                $transaction->items()->create([
                    'product_id'   => $productId,
                    'quantity'     => $qty,
                    'unit_price'   => $price,
                    'line_total'   => $qty * $price,
                    'pawn_item_id' => null,
                    'repair_id'    => null,
                ]);

                $product->decrement('quantity', $qty);
            }
        });

        return redirect()
            ->route('staff.transactions.index')
            ->with([
                'success'                 => 'Buy transaction created successfully.',
                'download_transaction_id' => $transaction->id,
            ]);
    }

    /* ----------------------------------------------------
     | DOWNLOAD – PDF (ONLY BUY)
     ---------------------------------------------------- */
    public function download(Transaction $transaction)
    {
        $this->assertBuyTransaction($transaction);

        $transaction->load(['items.product', 'customer']);

        $pdf = Pdf::loadView('staff.transactions.receipt', [
                'transaction' => $transaction,
            ])
            ->setPaper('a4', 'portrait');

        $fileName = 'buy_' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($fileName);
    }

    /* ----------------------------------------------------
     | SHOW – View (ONLY BUY)
     ---------------------------------------------------- */
    public function show(Transaction $transaction)
    {
        $this->assertBuyTransaction($transaction);

        $transaction->load(['items.product', 'customer']);

        return view('staff.transactions.show', compact('transaction'));
    }

    /* ----------------------------------------------------
     | RECEIPT – HTML view (ONLY BUY)
     ---------------------------------------------------- */
    public function receipt(Transaction $transaction)
    {
        $this->assertBuyTransaction($transaction);

        $transaction->load(['items.product', 'customer']);

        return view('staff.transactions.receipt', compact('transaction'));
    }

    /* ----------------------------------------------------
     | Guard – ONLY allow staff owner + BUY type
     ---------------------------------------------------- */
    private function assertBuyTransaction(Transaction $transaction): void
    {
        abort_if($transaction->staff_id !== auth()->id(), 403);
        abort_if($transaction->type !== self::TYPE_BUY, 403);
    }
}
