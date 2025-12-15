<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PawnItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PawnItemController extends Controller
{
    private float $monthlyPenaltyRate = 0.03; // 3% per month overdue

    /**
     * List pawn items.
     */
    public function index(Request $request)
    {
        $q       = $request->string('q')->trim()->toString();
        $status  = $request->string('status')->trim()->toString();
        $dueDate = $request->input('due_date'); // YYYY-MM-DD

        $today = Carbon::today();

        $pawnItems = PawnItem::query()
            ->with(['customer', 'pictures'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhereHas('customer', function ($cq) use ($q) {
                            $cq->where('name', 'like', "%{$q}%")
                               ->orWhere('email', 'like', "%{$q}%")
                               ->orWhere('contact_no', 'like', "%{$q}%");
                        });
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($dueDate, fn ($query) => $query->whereDate('due_date', '<=', $dueDate))
            ->latest()
            ->paginate(10)
            ->appends($request->only('q', 'status', 'due_date'));

        // IMPORTANT: Ensure computed fields exist on the items rendered in Blade
        $pawnItems->setCollection(
            $pawnItems->getCollection()->map(function (PawnItem $item) use ($today) {
                $this->attachComputedTotals($item, $today);
                return $item;
            })
        );

        return view('admin.pawn.index', compact('pawnItems', 'q', 'status', 'dueDate'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $pawnItem  = new PawnItem();
        $isEdit    = false;
        $customers = Customer::orderBy('name')->get(['id', 'name', 'email', 'contact_no']);

        return view('admin.pawn.form', compact('pawnItem', 'customers', 'isEdit'));
    }

    /**
     * Store pawn item (supports existing/new customer tabs).
     */
    public function store(Request $request)
    {
        $mode = $request->input('customer_mode'); // existing | new

        $rules = [
            'customer_mode'  => ['required', Rule::in(['existing', 'new'])],

            'title'          => ['required', 'string', 'max:190'],
            'description'    => ['nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:1'],
            'interest_cost'  => ['nullable', 'numeric', 'min:0'],
            'due_date'       => ['nullable', 'date'],
            'status'         => ['required', 'string', 'max:50'],

            'images'         => ['nullable', 'array'],
            'images.*'       => ['nullable', 'image', 'max:4096'],
        ];

        if ($mode === 'existing') {
            $rules['customer_id'] = ['required', 'integer', Rule::exists('customers', 'id')];
        } else {
            $rules['customer_name']       = ['required', 'string', 'max:255'];
            $rules['customer_email']      = ['required', 'email', 'max:255', Rule::unique('customers', 'email')];
            $rules['customer_contact_no'] = ['nullable', 'string', 'max:20'];
        }

        $messages = [
            'customer_email.unique' => 'This email already exists. Please use the "Existing Customer" tab and select the customer.',
        ];

        $validated = $request->validate($rules, $messages);

        if (empty($validated['due_date'])) {
            $validated['due_date'] = Carbon::now()->addMonths(3)->toDateString();
        }

        DB::transaction(function () use ($request, $validated, $mode) {
            $customerId = $this->resolveCustomerId($validated, $mode);

            $pawnItem = PawnItem::create([
                'customer_id'    => $customerId,
                'title'          => $validated['title'],
                'description'    => $validated['description'] ?? null,
                'price'          => $validated['price'],
                'interest_cost'  => $validated['interest_cost'] ?? 0,
                'due_date'       => $validated['due_date'],
                'status'         => $validated['status'],
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    if (! $file) continue;

                    $path = $file->store('pawn-items', 'public');

                    $pawnItem->pictures()->create([
                        'url' => $path,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.pawn.index')
            ->with('success', 'Pawn item added successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(PawnItem $pawnItem)
    {
        $isEdit    = true;
        $customers = Customer::orderBy('name')->get(['id', 'name', 'email', 'contact_no']);

        $pawnItem->load('pictures', 'customer');

        return view('admin.pawn.form', compact('pawnItem', 'customers', 'isEdit'));
    }

    /**
     * Update pawn item.
     */
    public function update(Request $request, PawnItem $pawnItem)
    {
        $mode = $request->input('customer_mode'); // existing | new

        $rules = [
            'customer_mode'  => ['required', Rule::in(['existing', 'new'])],

            'title'          => ['required', 'string', 'max:190'],
            'description'    => ['nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:1'],
            'interest_cost'  => ['nullable', 'numeric', 'min:0'],
            'due_date'       => ['nullable', 'date'],
            'status'         => ['required', 'string', 'max:50'],

            'images'         => ['nullable', 'array'],
            'images.*'       => ['nullable', 'image', 'max:4096'],

            'remove_images'  => ['nullable', 'array'],
            'remove_images.*'=> ['integer'],
        ];

        if ($mode === 'existing') {
            $rules['customer_id'] = ['required', 'integer', Rule::exists('customers', 'id')];
        } else {
            $rules['customer_name']       = ['required', 'string', 'max:255'];
            $rules['customer_email']      = ['required', 'email', 'max:255', Rule::unique('customers', 'email')];
            $rules['customer_contact_no'] = ['nullable', 'string', 'max:20'];
        }

        $messages = [
            'customer_email.unique' => 'This email already exists. Please use the "Existing Customer" tab and select the customer.',
        ];

        $validated = $request->validate($rules, $messages);

        if (empty($validated['due_date'])) {
            $validated['due_date'] = Carbon::now()->addMonths(3)->toDateString();
        }

        DB::transaction(function () use ($request, $pawnItem, $validated, $mode) {
            $customerId = $this->resolveCustomerId($validated, $mode);

            $pawnItem->update([
                'customer_id'    => $customerId,
                'title'          => $validated['title'],
                'description'    => $validated['description'] ?? null,
                'price'          => $validated['price'],
                'interest_cost'  => $validated['interest_cost'] ?? 0,
                'due_date'       => $validated['due_date'],
                'status'         => $validated['status'],
            ]);

            $removeIds = $request->input('remove_images', []);
            if (! empty($removeIds)) {
                $pics = $pawnItem->pictures()->whereIn('id', $removeIds)->get();
                foreach ($pics as $pic) {
                    if ($pic->url) Storage::disk('public')->delete($pic->url);
                    $pic->delete();
                }
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    if (! $file) continue;

                    $path = $file->store('pawn-items', 'public');

                    $pawnItem->pictures()->create([
                        'url' => $path,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.pawn.index')
            ->with('success', 'Pawn item updated successfully.');
    }

    /**
     * Delete pawn item.
     */
    public function destroy(PawnItem $pawnItem)
    {
        $pawnItem->load('pictures');

        DB::transaction(function () use ($pawnItem) {
            foreach ($pawnItem->pictures as $pic) {
                if ($pic->url) Storage::disk('public')->delete($pic->url);
                $pic->delete();
            }
            $pawnItem->delete();
        });

        return back()->with('success', 'Pawn item deleted successfully.');
    }

    /**
     * Redeem pawn item -> creates a Transaction (type: Pawn) using computed TOTAL TO PAY.
     */
    public function redeem(PawnItem $pawnItem)
    {
        if ($pawnItem->status === 'redeemed') {
            return back()->with('info', 'This pawn item is already redeemed.');
        }

        $today = Carbon::today();
        $calc  = $this->computeTotals($pawnItem, $today);

        DB::transaction(function () use ($pawnItem, $calc) {
            $transaction = Transaction::create([
                'customer_id' => $pawnItem->customer_id,
                'staff_id'    => auth()->id(),
                'type'        => 'Pawn',
            ]);

            $transaction->items()->create([
                'product_id'   => null,
                'pawn_item_id' => $pawnItem->id,
                'repair_id'    => null,
                'quantity'     => 1,
                'unit_price'   => (float) $calc['to_pay'],
                'line_total'   => (float) $calc['to_pay'],
            ]);

            PawnItem::whereKey($pawnItem->id)->update([
                'status' => 'redeemed',
            ]);
        });

        return back()->with('success', 'Pawn item redeemed and transaction recorded.');
    }

    private function resolveCustomerId(array $validated, string $mode): int
    {
        if ($mode === 'existing') {
            return (int) $validated['customer_id'];
        }

        $customer = Customer::create([
            'name'       => $validated['customer_name'],
            'email'      => $validated['customer_email'],
            'contact_no' => $validated['customer_contact_no'] ?? '',
        ]);

        return (int) $customer->id;
    }

    /**
     * Compute totals:
     * penalty = principal * 0.03 * monthsOverdue
     * computed_interest = base_interest + penalty
     * to_pay = principal + computed_interest
     */
    private function computeTotals(PawnItem $item, Carbon $today): array
    {
        $principal    = (float) $item->price;
        $baseInterest = (float) ($item->interest_cost ?? 0);

        $monthsOverdue = 0;
        $isOverdue     = false;

        if ($item->due_date) {
            $due = $item->due_date instanceof Carbon
                ? $item->due_date->copy()->startOfDay()
                : Carbon::parse($item->due_date)->startOfDay();

            $today = $today->copy()->startOfDay();

            if ($today->gt($due)) {
                $daysOverdue   = $due->diffInDays($today);
                $monthsOverdue = (int) ceil($daysOverdue / 30); // every month passed by
                $isOverdue     = $monthsOverdue > 0;
            }
        }

        $penalty          = $monthsOverdue * $this->monthlyPenaltyRate * $principal;
        $computedInterest = $baseInterest + $penalty;
        $toPay            = $principal + $computedInterest;

        return [
            'principal'         => $principal,
            'base_interest'     => $baseInterest,
            'months_overdue'    => $monthsOverdue,
            'penalty'           => $penalty,
            'computed_interest' => $computedInterest,
            'to_pay'            => $toPay,
            'is_overdue'        => $isOverdue,
        ];
    }

    private function attachComputedTotals(PawnItem $item, Carbon $today): void
    {
        $calc = $this->computeTotals($item, $today);

        // NOT DB columns — just runtime values for the index UI
        $item->computed_interest = $calc['computed_interest'];
        $item->to_pay            = $calc['to_pay'];
        $item->is_overdue        = $calc['is_overdue'];

        // optional debug display
        $item->months_overdue    = $calc['months_overdue'];
        $item->penalty           = $calc['penalty'];
    }
}
