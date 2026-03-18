<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $requests = Auth::user()->leaveRequests()
            ->with('leaveType')
            ->latest()
            ->paginate(15);

        return view('leave-requests.index', compact('requests'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $balances = Auth::user()->leaveBalances()
            ->with('leaveType')
            ->where('year', now()->year)
            ->get()
            ->keyBy('leave_type_id');

        return view('leave-requests.create', compact('leaveTypes', 'balances'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
        ]);

        $totalDays = $this->calculateWorkingDays(
            $validated['start_date'],
            $validated['end_date']
        );

        if ($totalDays <= 0) {
            return back()->withErrors(['start_date' => 'No working days in selected range.']);
        }

        $balance = Auth::user()->getLeaveBalance($validated['leave_type_id']);
        if ($balance && $balance->remaining_days < $totalDays) {
            return back()->withErrors(['leave_type_id' => 'Insufficient leave balance.']);
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        if ($balance) {
            $balance->increment('pending_days', $totalDays);
        }

        return redirect()->route('leave-requests.show', $leaveRequest)
            ->with('success', 'Leave request submitted successfully.');
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $this->authorize('view', $leaveRequest);
        $leaveRequest->load(['user', 'leaveType', 'reviewer']);

        return view('leave-requests.show', compact('leaveRequest'));
    }

    public function cancel(LeaveRequest $leaveRequest)
    {
        $this->authorize('cancel', $leaveRequest);

        if (!$leaveRequest->isPending()) {
            return back()->withErrors(['status' => 'Only pending requests can be cancelled.']);
        }

        $balance = Auth::user()->getLeaveBalance($leaveRequest->leave_type_id);
        if ($balance) {
            $balance->decrement('pending_days', $leaveRequest->total_days);
        }

        $leaveRequest->update(['status' => 'cancelled']);

        return redirect()->route('leave-requests.index')
            ->with('success', 'Leave request cancelled.');
    }

    private function calculateWorkingDays(string $startDate, string $endDate): float
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->pluck('date');

        $days = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if (!$current->isWeekend() && !$holidays->contains($current->toDateString())) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }
}
