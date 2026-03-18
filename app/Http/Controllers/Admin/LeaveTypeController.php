<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::withCount('leaveRequests')->get();
        return view('admin.leave-types.index', compact('leaveTypes'));
    }

    public function create()
    {
        return view('admin.leave-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:leave_types',
            'color' => 'required|string|size:7',
            'description' => 'nullable|string|max:500',
            'days_per_year' => 'required|integer|min:0|max:365',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
        ]);

        LeaveType::create($validated);

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Leave type created successfully.');
    }

    public function edit(LeaveType $leaveType)
    {
        return view('admin.leave-types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:leave_types,name,' . $leaveType->id,
            'color' => 'required|string|size:7',
            'description' => 'nullable|string|max:500',
            'days_per_year' => 'required|integer|min:0|max:365',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $leaveType->update($validated);

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Leave type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();
        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Leave type deleted.');
    }
}
