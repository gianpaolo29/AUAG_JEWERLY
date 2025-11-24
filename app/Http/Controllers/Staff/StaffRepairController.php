<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Repair;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StaffRepairController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();

        $repairs = Repair::query()
            ->with(['customer', 'picture'])
            ->when($q, function ($query) use ($q) {
                $query->where('description', 'like', "%{$q}%")
                      ->orWhereHas('customer', function ($q2) use ($q) {
                          $q2->where('name', 'like', "%{$q}%");
                      });
            })
            ->when($status, fn($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('staff.repairs.index', compact('repairs', 'q', 'status'));
    }

    public function create()
    {
        $repair = new Repair;
        $isEdit = false;

        $customers = User::where('role', 'customer')
            ->orderBy('name')
            ->get(['id','name','email']);

        return view('staff.repairs.form', compact('repair','customers','isEdit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'image' => 'nullable|image|max:4096',
        ]);

        $repair = Repair::create($validated);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('repairs', 'public');
            $repair->picture()->create(['url' => $path]);
        }

        return redirect()->route('staff.repairs.index')
            ->with('success', 'Repair created successfully.');
    }

    public function edit(Repair $repair)
    {
        $isEdit = true;

        $customers = User::where('role','customer')
            ->orderBy('name')
            ->get(['id','name','email']);

        $repair->load('picture');

        return view('staff.repairs.form', compact('repair','customers','isEdit'));
    }

    public function update(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'description' => 'required|string|max:1000',
            'price' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'image' => 'nullable|image|max:4096',
        ]);

        $repair->update($validated);

        if ($request->hasFile('image')) {
            if ($repair->picture) {
                Storage::disk('public')->delete($repair->picture->url);
                $repair->picture->delete();
            }

            $path = $request->file('image')->store('repairs', 'public');
            $repair->picture()->create(['url' => $path]);
        }

        return redirect()->route('staff.repairs.index')
            ->with('success', 'Repair updated successfully.');
    }

    public function destroy(Repair $repair)
    {
        if ($repair->picture) {
            Storage::disk('public')->delete($repair->picture->url);
            $repair->picture->delete();
        }

        $repair->delete();

        return redirect()->route('staff.repairs.index')
            ->with('success', 'Repair deleted.');
    }

    public function markComplete(Repair $repair)
    {
        if ($repair->status === 'completed') {
            return back()->with('info', 'Already completed.');
        }

        DB::transaction(function () use ($repair) {

            $repair->update(['status' => 'completed']);

            $transaction = Transaction::create([
                'customer_id' => $repair->customer_id,
                'staff_id' => auth()->id(),
                'type' => 'Repair',
            ]);

            $transaction->items()->create([
                'product_id' => null,
                'pawn_item_id' => null,
                'repair_id' => $repair->id,
                'quantity' => 1,
                'unit_price' => $repair->price,
                'line_total' => $repair->price,
            ]);
        });

        return back()->with('success', 'Repair Mark As Complete.');
    }
}
