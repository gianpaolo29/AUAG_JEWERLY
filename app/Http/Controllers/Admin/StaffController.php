<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    protected string $role = 'staff';

    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $users = User::where('role', $this->role)
            ->when($q, fn ($qr) => $qr->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
            )
            ->orderBy('name')
            ->paginate(10);

        return view('admin.staff.index', compact('users', 'q'));
    }

    public function create()
    {
        $user = new User(['role' => $this->role]);

        return view('admin.staff.form', compact('user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $validated['role'] = $this->role;
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff created successfully.');
    }

    public function edit(User $staff)
    {
        return view('admin.staff.form', ['user' => $staff]);
    }

    public function update(Request $request, User $staff)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($staff->id)],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $validated['role'] = $this->role;

        if ($validated['password'] ?? false) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $staff->update($validated);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff updated successfully.');
    }

    public function destroy(User $staff)
    {
        $staff->delete();

        return back()->with('success', 'Staff deleted successfully.');
    }
}
