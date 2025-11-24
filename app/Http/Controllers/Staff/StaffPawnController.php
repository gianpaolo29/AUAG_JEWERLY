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
        $pawnItems = PawnItem::with(['customer', 'pictures'])
            ->where('staff_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('staff.pawn.index', compact('pawnItems'));
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
