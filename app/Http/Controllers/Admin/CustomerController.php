<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $sort = (string) $request->query('sort', 'name');
        $dir  = strtolower((string) $request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortable = ['name', 'email', 'contact_no', 'created_at'];
        if (! in_array($sort, $sortable, true)) {
            $sort = 'name';
        }

        $customers = Customer::query()
            ->select(['id', 'name', 'email', 'contact_no', 'created_at'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('contact_no', 'like', "%{$q}%");
                });
            })
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'q', 'sort', 'dir', 'perPage'));
    }

    public function create()
    {
        $customer = new Customer();
        return view('admin.customers.form', compact('customer'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        Customer::create($validated);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $this->validated($request, $customer);

        $customer->update($validated);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return back()->with('success', 'Customer deleted successfully.');
    }

    protected function validated(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:150',
                Rule::unique('customers', 'email')->ignore($customer?->id),
            ],
            'contact_no' => ['nullable', 'string', 'max:30'],
        ]);
    }
}
