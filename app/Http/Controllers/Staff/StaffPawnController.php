<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PawnItem;         // <- adjust if your model name differs
use App\Models\PictureUrl;      // <- adjust if your picture model differs
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class StaffPawnController extends Controller
{
    public function index(Request $request)
    {
        $q       = $request->string('q')->toString();
        $status  = $request->string('status')->toString();
        $dueDate = $request->input('due_date');

        $pawnItems = PawnItem::query()
            ->with('customer')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->whereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%"))
                       ->orWhere('title', 'like', "%{$q}%");
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($dueDate, fn ($query) => $query->whereDate('due_date', $dueDate))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('staff.pawn.index', compact('pawnItems'));
    }


    public function create(Request $request)
    {
        $customers = Customer::select('id','name','email','contact_no')
            ->orderBy('name')
            ->get();

        // ✅ so form.blade.php won't crash
        $pawnItems = PawnItem::query()
            ->with('customer')
            ->latest('created_at')
            ->paginate(10);

        return view('staff.pawn.form', compact('customers', 'pawnItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_mode' => ['required', 'in:existing,new'],

            // existing
            'customer_id'   => ['nullable', 'required_if:customer_mode,existing', 'exists:customers,id'],

            // new
            'customer_name'  => ['nullable', 'required_if:customer_mode,new', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],

            // pawn fields
            'due_date'      => ['required', 'date', 'after_or_equal:loan_date'],
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string'],
            'price'         => ['required', 'numeric', 'min:1'],
            'interest_cost' => ['nullable', 'numeric', 'min:0'],

            'images.*'      => ['nullable', 'image', 'max:5120'],
        ]);

        $pawn = DB::transaction(function () use ($request) {
            // Resolve customer
            $customerId = null;

            if ($request->customer_mode === 'existing') {
                $customerId = (int) $request->customer_id;
            } else {
                // Create (or reuse) by email/phone if present to avoid unique conflicts
                $name  = trim((string) $request->customer_name);
                $email = trim((string) $request->customer_email);
                $phone = trim((string) $request->customer_phone);

                $existing = null;

                if ($email !== '') {
                    $existing = Customer::where('email', $email)->first();
                }
                if (! $existing && $phone !== '') {
                    $existing = Customer::where('contact_no', $phone)->first();
                }

                $customer = $existing ?: Customer::create([
                    'name'       => $name,
                    'email'      => $email !== '' ? $email : null,
                    'contact_no' => $phone !== '' ? $phone : null,
                    'status'     => 'active',
                ]);

                $customerId = $customer->id;
            }

            // Create pawn
            $pawn = PawnItem::create([
                'customer_id'   => $customerId,
                'due_date'      => $request->due_date,
                'title'         => $request->title,
                'description'   => $request->description,
                'price'         => $request->price,
                'interest_cost' => $request->interest_cost ?? round(((float) $request->price) * 0.03, 2),
                'status'        => 'active',
            ]);

            // Images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $path = $img->store('pawn', 'public');

                    PictureUrl::create([
                        'imageable_id' => $pawn->id, // <- adjust FK column if different
                        'imageable_type' => 'pawn', 
                        'url'          => $path,
                    ]);
                }
            }

            return $pawn;
        });

        return redirect()
            ->route('staff.pawn.index')
            ->with('success', 'Pawn ticket saved. Downloading receipt...')
            ->with('download_pawn_id', $pawn->id);
    }

    public function download(PawnItem $pawn)
    {
        $pawn->load(['customer', 'pictures']);

        $pdf = Pdf::loadView('staff.pawn.receipt', [
            'pawn' => $pawn,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('pawn-receipt-' . $pawn->id . '.pdf');
    }

    public function redeem(PawnItem $pawn)
    {
        if ($pawn->status !== 'redeemed') {
            $pawn->update(['status' => 'redeemed']);
        }

        return back()->with('success', 'Pawn item redeemed.');
    }
}
