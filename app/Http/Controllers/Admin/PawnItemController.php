<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PawnItem;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PawnItemController extends Controller
{
    /**
     * List pawn items.
     */
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $status = $request->string('status')->toString();
        $dueDate = $request->date('due_date');

        $today = Carbon::today();

        $pawnItems = PawnItem::with(['customer', 'pictures'])
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($q2) use ($q) {
                        $q2->where('name', 'like', "%{$q}%");
                    });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($dueDate, fn ($query) => $query->whereDate('due_date', '<=', $dueDate))
            ->latest()
            ->paginate(10)
            ->through(function (PawnItem $item) use ($today) {
                $principal = (float) $item->price;
                $baseInterest = (float) $item->interest_cost;

                $monthsOverdue = 0;
                $isOverdue = false;

                if ($item->due_date && $today->gt($item->due_date)) {
                    // months overdue ≈ 0.03 * price * months
                    $daysOverdue = $item->due_date->diffInDays($today);
                    $monthsOverdue = (int) ceil($daysOverdue / 30);
                    $isOverdue = $monthsOverdue > 0;
                }

                $penalty = $monthsOverdue * 0.03 * $principal;   // 3% of price per month overdue
                $totalInterest = $baseInterest + $penalty;             // interest column
                $totalToPay = $principal + $totalInterest;           // To Pay column

                // attach computed values (not saved to DB)
                $item->computed_interest = $totalInterest;
                $item->to_pay = $totalToPay;
                $item->is_overdue = $isOverdue;

                return $item;
            });

        return view('admin.pawn.index', compact('pawnItems', 'q', 'status', 'dueDate'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $pawnItem = new PawnItem;
        $isEdit = false;

        $customers = User::where('role', 'customer')
            ->orderBy('name')
            ->get();

        // 👇 pass $pawnItem not $pawn
        return view('admin.pawn.form', compact('pawnItem', 'customers', 'isEdit'));
    }

    /**
     * Store pawn item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'title' => 'required|string|max:190',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'interest_cost' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'status' => 'required|string',
            'images.*' => 'nullable|image|max:4096',
        ]);

        // default due date = +3 months if empty
        if (empty($validated['due_date'])) {
            $validated['due_date'] = Carbon::now()->addMonths(3)->format('Y-m-d');
        }

        $pawnItem = PawnItem::create($validated);

        // save images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {

                $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // Move to public/pawn-items
                $file->move(public_path('pawn-items'), $imageName);

                // Save relative path to DB
                $pawnItem->pictures()->create([
                    'url' => 'pawn-items/' . $imageName,
                ]);
            }
        }

        return redirect()
            ->route('admin.pawn.index')
            ->with('success', 'Pawn item added successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(PawnItem $pawnItem)
    {
        $isEdit = true;

        $customers = User::where('role', 'customer')
            ->orderBy('name')
            ->get();

        // 👇 again, pass $pawnItem
        return view('admin.pawn.form', compact('pawnItem', 'customers', 'isEdit'));
    }

    /**
     * Update pawn item.
     */
    public function update(Request $request, PawnItem $pawnItem)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'title' => 'required|string|max:190',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'interest_cost' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'status' => 'required|string',
            'images.*' => 'nullable|image|max:4096',
            'remove_images' => 'array',
            'remove_images.*' => 'integer',
        ]);

        if (empty($validated['due_date'])) {
            $validated['due_date'] = Carbon::now()->addMonths(3)->format('Y-m-d');
        }

        $pawnItem->update($validated);

        // remove selected images
        $removeIds = $request->input('remove_images', []);
        if (! empty($removeIds)) {
            $pics = $pawnItem->pictures()->whereIn('id', $removeIds)->get();
            foreach ($pics as $pic) {
                Storage::disk('public')->delete($pic->url);
                $pic->delete();
            }
        }

        // add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('pawn-items', 'public');
                $pawnItem->pictures()->create(['url' => $path]);
            }
        }

        return redirect()
            ->route('admin.pawn.index')
            ->with('success', 'Pawn item updated successfully.');
    }

    /**
     * Delete pawn item.
     */
    public function destroy(PawnItem $pawnItem)
    {
        foreach ($pawnItem->pictures as $pic) {
            Storage::disk('public')->delete($pic->url);
            $pic->delete();
        }

        $pawnItem->delete();

        return back()->with('success', 'Pawn item deleted successfully.');
    }

    public function redeem(PawnItem $pawnItem)
    {
        // avoid double redeem
        if ($pawnItem->status === 'redeemed') {
            return back()->with('info', 'This pawn item is already redeemed.');
        }

        $today = Carbon::today();
        $principal = (float) $pawnItem->price;
        $baseInterest = (float) $pawnItem->interest_cost;

        $monthsOverdue = 0;

        if ($pawnItem->due_date && $today->gt($pawnItem->due_date)) {
            $daysOverdue = $pawnItem->due_date->diffInDays($today);
            $monthsOverdue = (int) ceil($daysOverdue / 30);
        }

        $penalty = $monthsOverdue * 0.03 * $principal;
        $totalInterest = $baseInterest + $penalty;
        $totalToPay = $principal + $totalInterest;

        // Create transaction for redemption
        $transaction = Transaction::create([
            'customer_id' => $pawnItem->customer_id,
            'staff_id' => auth()->id(),
            'type' => 'Pawn',
        ]);

        $transaction->items()->create([
            'product_id' => null,
            'pawn_item_id' => $pawnItem->id,
            'repair_id' => null,
            'quantity' => 1,
            'unit_price' => $totalToPay,
            'line_total' => $totalToPay,
        ]);

        $pawnItem->update(['status' => 'redeemed']);

        return back()->with('success', 'Pawn item redeemed and transaction recorded.');
    }
}
