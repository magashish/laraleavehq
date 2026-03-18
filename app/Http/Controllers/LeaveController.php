<?php

namespace App\Http\Controllers;

use App\Models\BankHoliday;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $leaves = LeaveRequest::with('employee')
            ->when(!$user->is_manager, fn($q) => $q->where('employee_id', $user->id))
            ->latest()
            ->get();

        $bankHolidays = BankHoliday::orderBy('date')->get();

        $allEmployees = $user->is_manager
            ? User::with(['leaveRequests' => fn($q) => $q->where('status', 'approved')])->orderBy('name')->get()
            : collect();

        $leavesData = $leaves->map(fn($l) => [
            'id'         => $l->id,
            'start_date' => $l->start_date->toDateString(),
            'end_date'   => $l->end_date->toDateString(),
            'days'       => $l->days,
            'reason'     => $l->reason,
            'status'     => $l->status,
            'employee'   => [
                'id'    => $l->employee->id,
                'name'  => $l->employee->name,
                'role'  => $l->employee->role,
                'color' => $l->employee->color,
            ],
        ]);

        return view('leave.index', compact('user', 'leaves', 'leavesData', 'bankHolidays', 'allEmployees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'required|string|max:500',
        ]);

        $user = Auth::user();

        // Only managers can book for others
        if (!$user->is_manager && $validated['employee_id'] != $user->id) {
            abort(403);
        }

        $bankHolidayDates = BankHoliday::pluck('date')
            ->map(fn($d) => $d->toDateString())
            ->toArray();

        $days = $this->countWorkingDays($validated['start_date'], $validated['end_date'], $bankHolidayDates);

        if ($days <= 0) {
            return back()->withErrors(['start_date' => 'No working days in the selected range.'])->withInput();
        }

        $employee = User::findOrFail($validated['employee_id']);
        $usedDays = $employee->leaveRequests()->where('status', 'approved')->sum('days');

        if ($usedDays + $days > $employee->days_allowed) {
            $remaining = $employee->days_allowed - $usedDays;
            return back()->withErrors(['start_date' => "Only {$remaining} days remaining."])->withInput();
        }

        // Managers self-approve
        $isManager = $user->is_manager;
        LeaveRequest::create([
            'employee_id'    => $validated['employee_id'],
            'start_date'     => $validated['start_date'],
            'end_date'       => $validated['end_date'],
            'days'           => $days,
            'reason'         => $validated['reason'],
            'status'         => $isManager ? 'approved' : 'pending',
            'approved_by_id' => $isManager ? $user->id : null,
            'approved_at'    => $isManager ? now() : null,
        ]);

        return redirect()->route('leave.index')->with('success', 'Leave ' . ($isManager ? 'booked' : 'requested') . ' successfully.');
    }

    public function update(Request $request, LeaveRequest $leave)
    {
        $user = Auth::user();

        if (!$user->is_manager) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $leave->update([
            'status'         => $validated['status'],
            'approved_by_id' => $user->id,
            'approved_at'    => now(),
        ]);

        return back()->with('success', 'Leave ' . $validated['status'] . '.');
    }

    public function destroy(LeaveRequest $leave)
    {
        $user = Auth::user();

        if (!$user->is_manager && $leave->employee_id !== $user->id) {
            abort(403);
        }

        $leave->delete();

        return back()->with('success', 'Leave request removed.');
    }

    private function countWorkingDays(string $start, string $end, array $bankHolidays): int
    {
        $count = 0;
        $current = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        while ($current->lte($endDate)) {
            if (!$current->isWeekend() && !in_array($current->toDateString(), $bankHolidays)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }
}
