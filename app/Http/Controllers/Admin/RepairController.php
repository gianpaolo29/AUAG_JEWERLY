<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RepairController extends Controller
{
    /**
     * Display a listing of repairs.
     */
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();

        $repairs = Repair::query()
            ->with(['customer', 'picture'])   // FIXED: picture, not pictures
            ->when($q, function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('id', $q)
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhereHas('customer', function ($q2) use ($q) {
                            $q2->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        });
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.repairs.index', compact('repairs', 'q', 'status'));
    }

    /**
     * Show the form for creating a new repair.
     */
    public function create()
    {
        $repair = new Repair;
        $isEdit = false;

        $customers = User::where('role', 'customer')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.repairs.form', compact('repair', 'customers', 'isEdit'));
    }

    /**
     * Store a newly created repair.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:users,id'],
            'description' => ['required', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $repair = Repair::create([
            'customer_id' => $validated['customer_id'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'status' => $validated['status'],
        ]);

        //fix save to public
        if ($request->hasFile('image')) {

            $file = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Move file to public/repairs
            $file->move(public_path('repairs'), $imageName);

            // Save relative URL to DB
            $repair->picture()->create([
                'url' => 'repairs/' . $imageName,
            ]);
        }
        $this->notifyAdmins(new NewRepairRequestNotification($repair));
        return redirect()
            ->route('admin.repairs.index')
            ->with('success', 'Repair created successfully.');
    }

    /**
     * Show the form for editing the specified repair.
     */
    public function edit(Repair $repair)
    {
        $isEdit = true;

        $customers = User::where('role', 'customer')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // Load single picture
        $repair->load('picture');

        return view('admin.repairs.form', compact('repair', 'customers', 'isEdit'));
    }

    /**
     * Update the specified repair.
     */
    public function update(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:users,id'],
            'description' => ['required', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $repair->update([
            'customer_id' => $validated['customer_id'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'status' => $validated['status'],
        ]);

        // Replace existing image
        if ($request->hasFile('image')) {

            // delete old image
            if ($repair->picture) {
                Storage::disk('public')->delete($repair->picture->url);
                $repair->picture->delete();
            }

            // store new file
            $path = $request->file('image')->store('repairs', 'public');

            $repair->picture()->create(['url' => $path]);
        }

        return redirect()
            ->route('admin.repairs.index')
            ->with('success', 'Repair updated successfully.');
    }

    /**
     * Remove the specified repair.
     */
    public function destroy(Repair $repair)
    {
        if ($repair->picture) {
            Storage::disk('public')->delete($repair->picture->url);
            $repair->picture->delete();
        }

        $repair->delete();

        return redirect()
            ->route('admin.repairs.index')
            ->with('success', 'Repair deleted successfully.');
    }

    public function markComplete(Repair $repair)
    {
        // If already completed, don't double-create a transaction
        if ($repair->status === 'completed') {
            return back()->with('info', 'This repair is already completed.');
        }

        DB::transaction(function () use ($repair) {
            // 1) Update status
            $repair->update([
                'status' => 'completed',
            ]);

            // 2) Create a transaction record
            $transaction = Transaction::create([
                'customer_id' => $repair->customer_id,
                'staff_id' => auth()->id(),   // current logged-in staff
                'type' => 'Repair',       // so you can filter later
            ]);

            // 3) Add a single transaction item linked to this repair
            $transaction->items()->create([
                'product_id' => null,
                'pawn_item_id' => null,
                'repair_id' => $repair->id,
                'quantity' => 1,
                'unit_price' => $repair->price,
                'line_total' => $repair->price,
            ]);
        });

        return back()->with('success', 'Transaction Completed');
    }
}
