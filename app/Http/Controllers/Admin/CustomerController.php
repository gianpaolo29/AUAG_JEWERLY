<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    protected string $role = 'customer';

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $users = User::where('role', $this->role)
            ->when($q, fn($qr) =>
                $qr->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
            )
            ->orderBy('name')
            ->paginate(10);

        return view('admin.customers.index', compact('users', 'q'));
    }

    public function create()
    {
        $user = new User(['role' => $this->role]);

        return view('admin.customers.form', compact('user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $validated['role'] = $this->role;
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit(User $customer)
    {
        return view('admin.customers.form', ['user' => $customer]);
    }

    public function update(Request $request, User $customer)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($customer->id)],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $validated['role'] = $this->role;

        if ($validated['password'] ?? false) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $customer->update($validated);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(User $customer)
    {
        $customer->delete();

        return back()->with('success', 'Customer deleted successfully.');
    }
}
