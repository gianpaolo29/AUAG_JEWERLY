<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Repair;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RepairController extends Controller
{
    /**
     * Display a listing of repairs.
     */
    public function index(Request $request)
    {
        $q      = $request->string('q')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $repairs = Repair::query()
            ->with(['customer', 'picture'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('description', 'like', "%{$q}%")
                        ->orWhereHas('customer', function ($cq) use ($q) {
                            $cq->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%")
                                ->orWhere('contact_no', 'like', "%{$q}%");
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

        $customers = Customer::orderBy('name')
            ->get(['id', 'name', 'email', 'contact_no']);

        return view('admin.repairs.form', compact('repair', 'customers', 'isEdit'));
    }

    /**
     * Store a newly created repair.
     */
    public function store(Request $request)
    {
        $mode = $request->input('customer_mode');

        $rules = [
            'customer_mode' => ['required', Rule::in(['existing', 'new'])],

            'description'   => ['required', 'string', 'max:1000'],
            'price'         => ['required', 'numeric', 'min:0'],
            'status'        => ['required', Rule::in(['pending', 'completed', 'cancelled'])],
            'image'         => ['nullable', 'image', 'max:4096'],
        ];

        if ($mode === 'existing') {
            $rules['customer_id'] = ['required', 'integer', Rule::exists('customers', 'id')];
        } else {
            $rules['customer_name']  = ['required', 'string', 'max:255'];

            // ✅ DUPLICATE CHECKS (New Customer)
            $rules['customer_email'] = [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email'),
            ];

            $rules['customer_phone'] = [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'contact_no'),
            ];
        }

        $messages = [
            'customer_mode.required' => 'Please choose customer type (Existing or New).',

            'customer_id.required'   => 'Please select an existing customer.',
            'customer_id.exists'     => 'Selected customer does not exist.',

            'customer_name.required' => 'Customer name is required for a new customer.',

            'customer_email.email'   => 'Please enter a valid email address.',
            'customer_email.unique'  => 'This email is already registered. Please use "Existing Customer" or enter a different email.',

            'customer_phone.unique'  => 'This contact number is already registered. Please use "Existing Customer" or enter a different number.',

            'description.required'   => 'Repair description is required.',
            'price.required'         => 'Repair price is required.',
            'price.numeric'          => 'Repair price must be a number.',
            'price.min'              => 'Repair price cannot be negative.',
            'status.required'        => 'Please select a status.',
            'status.in'              => 'Invalid status selected.',
            'image.image'            => 'Uploaded file must be an image.',
            'image.max'              => 'Image is too large. Max size is 4MB.',
        ];

        $validated = $request->validate($rules, $messages);

        $repair = null;

        DB::transaction(function () use (&$repair, $validated, $mode, $request) {
            if ($mode === 'existing') {
                $customerId = (int) $validated['customer_id'];
            } else {
                $customer = Customer::create([
                    'name'       => $validated['customer_name'],
                    'email'      => $validated['customer_email'] ?? null,
                    'contact_no' => $validated['customer_phone'] ?? null,
                ]);
                $customerId = $customer->id;
            }

            $repair = Repair::create([
                'customer_id' => $customerId,
                'description' => $validated['description'],
                'price'       => $validated['price'],
                'status'      => $validated['status'],
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('repairs', 'public');
                $repair->picture()->create(['url' => $path]);
            }
        });

        return redirect()
            ->route('admin.repairs.index')
            ->with([
                'success'            => 'Repair created successfully.',
                'download_repair_id' => $repair->id,
            ]);
    }

    /**
     * Show the form for editing the specified repair.
     */
    public function edit(Repair $repair)
    {
        $isEdit = true;

        $customers = Customer::orderBy('name')
            ->get(['id', 'name', 'email', 'contact_no']);

        $repair->load('picture', 'customer');

        return view('admin.repairs.form', compact('repair', 'customers', 'isEdit'));
    }

    /**
     * Update the specified repair.
     */
    public function update(Request $request, Repair $repair)
    {
        $mode = $request->input('customer_mode');

        $rules = [
            'customer_mode' => ['required', Rule::in(['existing', 'new'])],

            'description'   => ['required', 'string', 'max:1000'],
            'price'         => ['required', 'numeric', 'min:0'],
            'status'        => ['required', Rule::in(['pending', 'completed', 'cancelled'])],
            'image'         => ['nullable', 'image', 'max:4096'],
        ];

        if ($mode === 'existing') {
            $rules['customer_id'] = ['required', 'integer', Rule::exists('customers', 'id')];
        } else {
            $rules['customer_name']  = ['required', 'string', 'max:255'];

            // ✅ DUPLICATE CHECKS (New Customer)
            $rules['customer_email'] = [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email'),
            ];

            $rules['customer_phone'] = [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'contact_no'),
            ];
        }

        $messages = [
            'customer_mode.required' => 'Please choose customer type (Existing or New).',

            'customer_id.required'   => 'Please select an existing customer.',
            'customer_id.exists'     => 'Selected customer does not exist.',

            'customer_name.required' => 'Customer name is required for a new customer.',

            'customer_email.email'   => 'Please enter a valid email address.',
            'customer_email.unique'  => 'This email is already registered. Please use "Existing Customer" or enter a different email.',

            'customer_phone.unique'  => 'This contact number is already registered. Please use "Existing Customer" or enter a different number.',

            'description.required'   => 'Repair description is required.',
            'price.required'         => 'Repair price is required.',
            'price.numeric'          => 'Repair price must be a number.',
            'price.min'              => 'Repair price cannot be negative.',
            'status.required'        => 'Please select a status.',
            'status.in'              => 'Invalid status selected.',
            'image.image'            => 'Uploaded file must be an image.',
            'image.max'              => 'Image is too large. Max size is 4MB.',
        ];

        $validated = $request->validate($rules, $messages);

        DB::transaction(function () use ($repair, $validated, $mode, $request) {
            if ($mode === 'existing') {
                $customerId = (int) $validated['customer_id'];
            } else {
                $customer = Customer::create([
                    'name'       => $validated['customer_name'],
                    'email'      => $validated['customer_email'] ?? null,
                    'contact_no' => $validated['customer_phone'] ?? null,
                ]);
                $customerId = $customer->id;
            }

            $repair->update([
                'customer_id' => $customerId,
                'description' => $validated['description'],
                'price'       => $validated['price'],
                'status'      => $validated['status'],
            ]);

            if ($request->hasFile('image')) {
                if ($repair->picture) {
                    Storage::disk('public')->delete($repair->picture->url);
                    $repair->picture->delete();
                }

                $path = $request->file('image')->store('repairs', 'public');
                $repair->picture()->create(['url' => $path]);
            }
        });


        return redirect()
            ->route('admin.repairs.index')
            ->with([
                'success'            => 'Repair updated successfully.',
                'download_repair_id' => $repair->id,
            ]);
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
        if ($repair->status === 'completed') {
            return back()->with('info', 'Already completed.');
        }

        DB::transaction(function () use ($repair) {
            $repair->update(['status' => 'completed']);

            $transaction = Transaction::create([
                'customer_id' => $repair->customer_id,
                'staff_id'    => auth()->id(),
                'type'        => 'Repair',
            ]);

            $transaction->items()->create([
                'product_id'   => null,
                'pawn_item_id' => null,
                'repair_id'    => $repair->id,
                'quantity'     => 1,
                'unit_price'   => $repair->price,
                'line_total'   => $repair->price,
            ]);
        });

        return back()->with('success', 'Transaction Completed');
    }


    public function download(Repair $repair)
    {
        $repair->load(['customer', 'picture']);

        $pdf = Pdf::loadView('admin.repairs.receipt', [
            'repair' => $repair,
        ])
            ->setPaper('a4', 'portrait');

        $fileName = 'repair_' . str_pad($repair->id, 6, '0', STR_PAD_LEFT) . '.pdf';

        return $pdf->download($fileName);
    }

}
