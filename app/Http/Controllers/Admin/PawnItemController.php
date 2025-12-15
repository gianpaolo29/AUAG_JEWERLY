<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PawnItem;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PawnItemController extends Controller
{
    private float $monthlyPenaltyRate = 0.03; // 3% per month overdue

    public function index(Request $request)
    {
        $q        = $request->string('q')->trim()->toString();
        $status   = $request->string('status')->trim()->toString();
        $dueDate  = $request->input('due_date');  // YYYY-MM-DD
        $loanDate = $request->input('loan_date'); // YYYY-MM-DD

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
            ->when($loanDate, fn ($query) => $query->whereDate('loan_date', $loanDate))
            ->when($dueDate, fn ($query) => $query->whereDate('due_date', $dueDate))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Attach computed totals for UI
        $pawnItems->setCollection(
            $pawnItems->getCollection()->map(function (PawnItem $item) use ($today) {
                $this->attachComputedTotals($item, $today);
                return $item;
            })
        );

        return view('admin.pawn.index', compact('pawnItems'));
    }

    public function create()
    {
        $pawnItem  = new PawnItem();
        $isEdit    = false;
        $customers = Customer::query()
            ->select(['id', 'name', 'email', 'contact_no'])
            ->orderBy('name')
            ->get();

        return view('admin.pawn.form', compact('pawnItem', 'customers', 'isEdit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_mode' => ['required', Rule::in(['existing', 'new'])],

            // existing
            'customer_id'   => ['nullable', 'required_if:customer_mode,existing', 'integer', Rule::exists('customers', 'id')],

            // new
            'customer_name'       => ['nullable', 'required_if:customer_mode,new', 'string', 'max:255'],
            'customer_email'      => ['nullable', 'email', 'max:255'],
            'customer_contact_no' => ['nullable', 'string', 'max:20'],

            // pawn fields
            'due_date'      => ['nullable', 'date'],
            'title'         => ['required', 'string', 'max:190'],
            'description'   => ['nullable', 'string'],
            'price'         => ['required', 'numeric', 'min:1'],
            'interest_cost' => ['nullable', 'numeric', 'min:0'],
            'status'        => ['required', Rule::in(['active', 'redeemed', 'forfeited'])],

            'images'        => ['nullable', 'array'],
            'images.*'      => ['nullable', 'image', 'max:4096'],
        ]);

        $loanDate = !empty($validated['loan_date'])
            ? Carbon::parse($validated['loan_date'])->toDateString()
            : Carbon::today()->toDateString();

        $dueDate = !empty($validated['due_date'])
            ? Carbon::parse($validated['due_date'])->toDateString()
            : Carbon::parse($loanDate)->addMonths(3)->toDateString();

        if (Carbon::parse($dueDate)->lt(Carbon::parse($loanDate))) {
            throw ValidationException::withMessages([
                'due_date' => 'Due date must be the same as or after the loan date.',
            ]);
        }

        $pawn = DB::transaction(function () use ($request, $validated, $loanDate, $dueDate) {
            $customerId = $this->resolveCustomerId($validated);

            $principal = (float) $validated['price'];
            $interest  = array_key_exists('interest_cost', $validated) && $validated['interest_cost'] !== null
                ? (float) $validated['interest_cost']
                : round($principal * 0.03, 2);

            $pawn = PawnItem::create([
                'customer_id'   => $customerId,
                'due_date'      => $dueDate,
                'title'         => $validated['title'],
                'description'   => $validated['description'] ?? null,
                'price'         => $principal,
                'interest_cost' => $interest,
                'status'        => $validated['status'] ?? 'active',
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    if (! $file) continue;
                    $path = $file->store('pawn-items', 'public');
                    $pawn->pictures()->create(['url' => $path]);
                }
            }

            return $pawn;
        });

        return redirect()
            ->route('admin.pawn.index')
            ->with('success', 'Pawn item created. Downloading receipt...')
            ->with('download_pawn_id', $pawn->id);
    }

    public function edit(PawnItem $pawnItem)
    {
        $isEdit    = true;
        $customers = Customer::query()
            ->select(['id', 'name', 'email', 'contact_no'])
            ->orderBy('name')
            ->get();

        $pawnItem->load(['customer', 'pictures']);

        return view('admin.pawn.form', compact('pawnItem', 'customers', 'isEdit'));
    }

    public function update(Request $request, PawnItem $pawnItem)
    {
        $validated = $request->validate([
            'customer_mode' => ['required', Rule::in(['existing', 'new'])],

            // existing
            'customer_id'   => ['nullable', 'required_if:customer_mode,existing', 'integer', Rule::exists('customers', 'id')],

            // new
            'customer_name'       => ['nullable', 'required_if:customer_mode,new', 'string', 'max:255'],
            'customer_email'      => ['nullable', 'email', 'max:255'],
            'customer_contact_no' => ['nullable', 'string', 'max:20'],

            // pawn fields
            'due_date'      => ['nullable', 'date'],
            'title'         => ['required', 'string', 'max:190'],
            'description'   => ['nullable', 'string'],
            'price'         => ['required', 'numeric', 'min:1'],
            'interest_cost' => ['nullable', 'numeric', 'min:0'],
            'status'        => ['required', Rule::in(['active', 'redeemed', 'forfeited'])],

            'images'        => ['nullable', 'array'],
            'images.*'      => ['nullable', 'image', 'max:4096'],

            'remove_images'   => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ]);

        $loanDate = !empty($validated['loan_date'])
            ? Carbon::parse($validated['loan_date'])->toDateString()
            : ($pawnItem->loan_date ? Carbon::parse($pawnItem->loan_date)->toDateString() : Carbon::today()->toDateString());

        $dueDate = !empty($validated['due_date'])
            ? Carbon::parse($validated['due_date'])->toDateString()
            : Carbon::parse($loanDate)->addMonths(3)->toDateString();

        if (Carbon::parse($dueDate)->lt(Carbon::parse($loanDate))) {
            throw ValidationException::withMessages([
                'due_date' => 'Due date must be the same as or after the loan date.',
            ]);
        }

        DB::transaction(function () use ($request, $pawnItem, $validated, $loanDate, $dueDate) {
            $customerId = $this->resolveCustomerId($validated);

            $principal = (float) $validated['price'];
            $interest  = array_key_exists('interest_cost', $validated) && $validated['interest_cost'] !== null
                ? (float) $validated['interest_cost']
                : round($principal * 0.03, 2);

            $pawnItem->update([
                'customer_id'   => $customerId,
                'due_date'      => $dueDate,
                'title'         => $validated['title'],
                'description'   => $validated['description'] ?? null,
                'price'         => $principal,
                'interest_cost' => $interest,
                'status'        => $validated['status'],
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
                    $pawnItem->pictures()->create(['url' => $path]);
                }
            }
        });

        return redirect()
            ->route('admin.pawn.index')
            ->with('success', 'Pawn item updated. Downloading receipt...')
            ->with('download_pawn_id', $pawnItem->id);
    }

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

        return back()->with('success', 'Pawn item deleted.');
    }

    public function redeem(PawnItem $pawnItem)
    {
        if ($pawnItem->status === 'redeemed') {
            return back()->with('info', 'This pawn item is already redeemed.');
        }

        $today = Carbon::today();
        $calc  = $this->computeTotals($pawnItem, $today);

        $transaction = DB::transaction(function () use ($pawnItem, $calc) {
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

            $pawnItem->update(['status' => 'redeemed']);

            return $transaction;
        });

        return back()
            ->with('success', 'Redeemed. Transaction recorded.')
            ->with('download_transaction_id', $transaction->id);
    }

    public function download(PawnItem $pawnItem)
    {
        $pawnItem->load(['customer', 'pictures']);

        $today = Carbon::today();
        $calc  = $this->computeTotals($pawnItem, $today);

        $pdf = Pdf::loadView('admin.pawn.receipt', [
            'pawn' => $pawnItem,
            'calc' => $calc,
            'today' => $today,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('pawn-receipt-' . $pawnItem->id . '.pdf');
    }

    private function resolveCustomerId(array $validated): int
    {
        if (($validated['customer_mode'] ?? '') === 'existing') {
            return (int) $validated['customer_id'];
        }

        $name  = trim((string) ($validated['customer_name'] ?? ''));
        $email = trim((string) ($validated['customer_email'] ?? ''));
        $phone = trim((string) ($validated['customer_contact_no'] ?? ''));

        // reuse by email/phone if exists
        $existing = null;

        if ($email !== '') {
            $existing = Customer::where('email', $email)->first();
        }
        if (! $existing && $phone !== '') {
            $existing = Customer::where('contact_no', $phone)->first();
        }

        if ($existing) {
            return (int) $existing->id;
        }

        $customer = Customer::create([
            'name'       => $name,
            'email'      => $email !== '' ? $email : null,
            'contact_no' => $phone !== '' ? $phone : null,
            'status'     => 'active',
        ]);

        return (int) $customer->id;
    }

    /**
     * Overdue months:
     * - if today > due_date, monthsOverdue counts partial month as 1
     * penalty = principal * 0.03 * monthsOverdue
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

            $t = $today->copy()->startOfDay();

            if ($t->gt($due)) {
                $fullMonths = $due->diffInMonths($t);
                $anchor     = $due->copy()->addMonths($fullMonths);
                $monthsOverdue = $anchor->lt($t) ? $fullMonths + 1 : $fullMonths;
                $isOverdue = $monthsOverdue > 0;
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

        $item->computed_interest = $calc['computed_interest'];
        $item->to_pay            = $calc['to_pay'];
        $item->is_overdue        = $calc['is_overdue'];
        $item->months_overdue    = $calc['months_overdue'];
        $item->penalty           = $calc['penalty'];
    }
}
