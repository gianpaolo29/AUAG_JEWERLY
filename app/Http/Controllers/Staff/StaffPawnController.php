<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PawnItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class StaffPawnController extends Controller
{
    public function index(Request $request)
{
    $q = $request->string('q')->toString();
    $status = $request->string('status')->toString();
    $dueDate = $request->date('due_date');
    $today = \Carbon\Carbon::today();

    $pawnItems = PawnItem::with(['customer', 'pictures'])
        ->when($q, function ($query) use ($q) {
            $query->where('title', 'like', "%{$q}%")
                  ->orWhereHas('customer', function ($q2) use ($q) {
                      $q2->where('name', 'like', "%{$q}%");
                  });
        })
        ->when($status, fn($query) => $query->where('status', $status))
        ->when($dueDate, fn($query) => $query->whereDate('due_date', '<=', $dueDate))
        ->latest()
        ->paginate(10)
        ->through(function (PawnItem $item) use ($today) {

            $principal = (float) $item->price;
            $baseInterest = (float) $item->interest_cost;

            $monthsOverdue = 0;
            $isOverdue = false;

            if ($item->due_date && $today->gt($item->due_date)) {
                $daysOverdue = $item->due_date->diffInDays($today);
                $monthsOverdue = (int) ceil($daysOverdue / 30);
                $isOverdue = $monthsOverdue > 0;
            }

            $penalty = $monthsOverdue * 0.03 * $principal;
            $totalInterest = $baseInterest + $penalty;
            $totalToPay = $principal + $totalInterest;

            $item->computed_interest = $totalInterest;
            $item->to_pay = $totalToPay;
            $item->is_overdue = $isOverdue;

            return $item;
        });

    return view('staff.pawn.index', compact('pawnItems', 'q', 'status', 'dueDate'));
}


    public function create()
    {
        $customers = User::where('role', 'customer')
            ->orderBy('name')
            ->get();

        return view('staff.pawn.form', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'title' => 'required|string|max:190',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'interest_cost' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'images.*' => 'nullable|image|max:4096',
        ]);

        // Auto due date (3 months)
        if (empty($validated['due_date'])) {
            $validated['due_date'] = Carbon::now()->addMonths(3)->format('Y-m-d');
        }

        $validated['status'] = 'active';
        $validated['staff_id'] = auth()->id();

        $pawnItem = PawnItem::create($validated);

        // Save images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('pawn-items', 'public');
                $pawnItem->pictures()->create(['url' => $path]);
            }
        }

        return redirect()
            ->route('staff.pawn.index')
            ->with('success', 'Pawn item recorded.');
    }
}
