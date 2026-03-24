<?php

namespace App\Http\Controllers;

use App\Models\BankHoliday;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $leaves = LeaveRequest::with(['employee', 'leaveType'])
            ->when(!$user->isManager(), fn($q) => $q->where('employee_id', $user->id))
            ->latest()
            ->get();

        $bankHolidays = BankHoliday::orderBy('date')->get();
        $leaveTypes   = LeaveType::where('is_active', true)->orderBy('name')->get();

        $allEmployees = $user->isManager()
            ? User::with(['leaveRequests' => fn($q) => $q->where('status', 'approved')])->orderBy('name')->get()
            : collect();

        $leavesData = $leaves->map(fn($l) => [
            'id'         => $l->id,
            'start_date' => $l->start_date->toDateString(),
            'end_date'   => $l->end_date->toDateString(),
            'days'       => $l->days,
            'reason'     => $l->reason,
            'status'     => $l->status,
            'leave_type' => $l->leaveType ? [
                'id'    => $l->leaveType->id,
                'name'  => $l->leaveType->name,
                'color' => $l->leaveType->color,
            ] : null,
            'employee'   => [
                'id'    => $l->employee->id,
                'name'  => $l->employee->name,
                'role'  => $l->employee->role,
                'color' => $l->employee->color,
            ],
        ]);

        return view('leave.index', compact('user', 'leaves', 'leavesData', 'bankHolidays', 'allEmployees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'employee_id'   => 'required|exists:users,id',
            'leave_type_id' => 'nullable|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'nullable|string|max:500',
            'admin_override' => 'boolean',
        ]);

        // Only managers can book for others
        if (!$user->isManager() && $validated['employee_id'] != $user->id) {
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

        // Check holiday allowance (not applicable for contractors/interns)
        if ($employee->hasHolidayAllowance()) {
            $leaveType = $validated['leave_type_id']
                ? LeaveType::find($validated['leave_type_id'])
                : null;

            $countsTowardAllowance = !$leaveType || $leaveType->counts_toward_allowance;

            if ($countsTowardAllowance) {
                $usedDays = $employee->leaveRequests()->where('status', 'approved')->sum('days');
                if ($usedDays + $days > $employee->days_allowed) {
                    $remaining = $employee->days_allowed - $usedDays;
                    return back()->withErrors(['start_date' => "Only {$remaining} days remaining."])->withInput();
                }
            }
        }

        // Department concurrency check (skip if admin_override)
        $adminOverride = $user->isAdmin() && $request->boolean('admin_override');

        if (!$adminOverride) {
            $conflicts = $this->checkDepartmentConflicts($employee, $validated['start_date'], $validated['end_date']);
            if ($conflicts) {
                return back()->withErrors(['start_date' => $conflicts])->withInput();
            }
        }

        $isManager = $user->isManager();

        LeaveRequest::create([
            'employee_id'    => $validated['employee_id'],
            'leave_type_id'  => $validated['leave_type_id'] ?? null,
            'start_date'     => $validated['start_date'],
            'end_date'       => $validated['end_date'],
            'days'           => $days,
            'reason'         => $validated['reason'] ?? '',
            'status'         => $isManager ? 'approved' : 'pending',
            'approved_by_id' => $isManager ? $user->id : null,
            'approved_at'    => $isManager ? now() : null,
            'admin_override' => $adminOverride,
        ]);

        return redirect()->route('leave.index')
            ->with('success', 'Leave ' . ($isManager ? 'booked' : 'requested') . ' successfully.');
    }

    public function update(Request $request, LeaveRequest $leave)
    {
        $user = Auth::user();

        if (!$user->isManager()) abort(403);

        $validated = $request->validate([
            'status'         => 'required|in:approved,rejected',
            'admin_override' => 'boolean',
        ]);

        // Department concurrency check when approving (skip if admin_override)
        $adminOverride = $user->isAdmin() && $request->boolean('admin_override');

        if ($validated['status'] === 'approved' && !$adminOverride) {
            $conflicts = $this->checkDepartmentConflicts(
                $leave->employee,
                $leave->start_date->toDateString(),
                $leave->end_date->toDateString(),
                $leave->id
            );
            if ($conflicts) {
                return back()->withErrors(['department' => $conflicts]);
            }
        }

        $leave->update([
            'status'         => $validated['status'],
            'approved_by_id' => $user->id,
            'approved_at'    => now(),
            'admin_override' => $adminOverride,
        ]);

        return back()->with('success', 'Leave ' . $validated['status'] . '.');
    }

    public function destroy(LeaveRequest $leave)
    {
        $user = Auth::user();

        if (!$user->isManager() && $leave->employee_id !== $user->id) {
            abort(403);
        }

        $leave->delete();

        return back()->with('success', 'Leave request removed.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Check if approving leave for an employee would breach any department concurrency limits.
     * Returns an error message string, or null if all is fine.
     */
    private function checkDepartmentConflicts(User $employee, string $startDate, string $endDate, ?int $excludeLeaveId = null): ?string
    {
        $departments = $employee->departments;

        foreach ($departments as $dept) {
            $concurrent = $dept->concurrentAbsences($startDate, $endDate, $excludeLeaveId);
            if ($concurrent >= $dept->max_concurrent) {
                return "Cannot approve: {$dept->name} already has {$concurrent} of {$dept->max_concurrent} allowed concurrent absence(s) during this period. An Admin can override this.";
            }
        }

        return null;
    }

    private function countWorkingDays(string $start, string $end, array $bankHolidays): int
    {
        $count   = 0;
        $current = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        while ($current->lte($endDate)) {
            if (!$current->isWeekend()) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }
}
