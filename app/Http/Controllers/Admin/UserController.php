<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('manager')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $managers = User::whereIn('role', ['admin', 'manager'])->get();
        return view('admin.users.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,employee',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        $this->allocateLeaveBalances($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'Employee created successfully.');
    }

    public function edit(User $user)
    {
        $managers = User::whereIn('role', ['admin', 'manager'])->where('id', '!=', $user->id)->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $balances = $user->leaveBalances()->with('leaveType')->where('year', now()->year)->get()->keyBy('leave_type_id');

        return view('admin.users.edit', compact('user', 'managers', 'leaveTypes', 'balances'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,manager,employee',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $user->update($validated);

        // Update leave balances if provided
        if ($request->has('balances')) {
            foreach ($request->input('balances', []) as $leaveTypeId => $days) {
                LeaveBalance::updateOrCreate(
                    ['user_id' => $user->id, 'leave_type_id' => $leaveTypeId, 'year' => now()->year],
                    ['allocated_days' => $days]
                );
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Employee updated successfully.');
    }

    private function allocateLeaveBalances(User $user): void
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();
        foreach ($leaveTypes as $leaveType) {
            LeaveBalance::create([
                'user_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'year' => now()->year,
                'allocated_days' => $leaveType->days_per_year,
                'used_days' => 0,
                'pending_days' => 0,
            ]);
        }
    }
}
