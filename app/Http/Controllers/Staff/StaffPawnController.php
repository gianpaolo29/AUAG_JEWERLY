<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PawnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class StaffPawnController extends Controller
{
    private float $monthlyPenaltyRate = 0.03;

    public function index(Request $request)
    {
        $q       = $request->string('q')->trim()->toString();
        $status  = $request->string('status')->trim()->toString();
        $dueDate = $request->input('due_date'); // YYYY-MM-DD
        $today   = Carbon::today();

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
            ->appends($request->only('q', 'status', 'due_date'))
            ->through(function (PawnItem $item) use ($today) {
                $this->attachComputedTotals($item, $today);
                return $item;
            });

        return view('staff.pawn.index', compact('pawnItems', 'q', 'status', 'dueDate'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get(['id', 'name', 'email', 'contact_no']);
        return view('staff.pawn.form', compact('customers'));
    }

    /**
     * STORE -> redirect to index and auto-download PDF
     */
    public function store(Request $request)
    {
        $mode = $request->input('customer_mode'); // existing | new

        $rules = [
            'customer_mode' => ['required', Rule::in(['existing', 'new'])],

            // required all pawn details
            'loan_date'     => ['required', 'date'],
            'due_date'      => ['required', 'date'],
            'title'         => ['required', 'string', 'max:190'],
            'description'   => ['required', 'string'],
            'price'         => ['required', 'numeric', 'min:1'],
            'interest_cost' => ['required', 'numeric', 'min:0'],

            'images'        => ['nullable', 'array'],
            'images.*'      => ['nullable', 'image', 'max:4096'],
        ];

        if ($mode === 'existing') {
            $rules['customer_id'] = ['required', 'integer', Rule::exists('customers', 'id')];
        } else {
            $rules['customer_name']  = ['required', 'string', 'max:255'];
            $rules['customer_email'] = ['required', 'email', 'max:255', Rule::unique('customers', 'email')];
            $rules['customer_phone'] = ['required', 'string', 'max:20'];
        }

        $messages = [
            'customer_email.unique' => 'This email already exists. Please use the "Select Existing" tab.',
        ];

        $validated = $request->validate($rules, $messages);

        $pawnItem = null;

        DB::transaction(function () use ($request, $validated, $mode, &$pawnItem) {
            // Resolve customer_id
            if ($mode === 'existing') {
                $customerId = (int) $validated['customer_id'];
            } else {
                $customer = Customer::create([
                    'name'       => $validated['customer_name'],
                    'email'      => $validated['customer_email'],
                    'contact_no' => $validated['customer_phone'], // IMPORTANT: contact_no in DB
                ]);
                $customerId = (int) $customer->id;
            }

            $pawnItem = new PawnItem([
                'customer_id'   => $customerId,
                'title'         => $validated['title'],
                'description'   => $validated['description'],
                'price'         => $validated['price'],
                'interest_cost' => $validated['interest_cost'],
                'due_date'      => $validated['due_date'],
                'status'        => 'active',
            ]);

            // use loan_date as created_at (pawn date)
            $pawnItem->created_at = Carbon::parse($validated['loan_date'])
                ->setTimeFromTimeString(now()->format('H:i:s'));

            $pawnItem->save();

            // save images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    if (! $file) continue;
                    $path = $file->store('pawn-items', 'public');
                    $pawnItem->pictures()->create(['url' => $path]);
                }
            }
        });

        return redirect()
            ->route('staff.pawn.index')
            ->with([
                'success'          => 'Pawn ticket saved successfully.',
                'download_pawn_id' => $pawnItem->id,
            ]);
    }

    /**
     * DOWNLOAD -> PDF pawn receipt
     */
    public function download(PawnItem $pawnItem)
    {

        $pawnItem->load(['customer', 'pictures']);

        $today = Carbon::today();
        $calc  = $this->computeTotals($pawnItem, $today);

        $pdf = Pdf::loadView('staff.pawn.receipt', [
                'pawnItem' => $pawnItem,
                'calc'     => $calc,
            ])
            ->setPaper('a4', 'portrait');

        $fileName = 'pawn_ticket_' . str_pad($pawnItem->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($fileName);
    }

    /* ---------------- Totals helpers ---------------- */

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
                $monthsOverdue = (int) ceil($daysOverdue / 30);
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
            'monthly_rate'      => $this->monthlyPenaltyRate,
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
