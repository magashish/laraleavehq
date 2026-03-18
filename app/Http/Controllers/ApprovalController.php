<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = LeaveRequest::with(['user', 'leaveType'])
            ->where('status', 'pending');

        if (!$user->isAdmin()) {
            $query->whereHas('user', fn($q) => $q->where('manager_id', $user->id));
        }

        $requests = $query->latest()->paginate(15);

        return view('approvals.index', compact('requests'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorize('review', $leaveRequest);

        $validated = $request->validate([
            'reviewer_comment' => 'nullable|string|max:500',
        ]);

        $balance = $leaveRequest->user->getLeaveBalance($leaveRequest->leave_type_id);
        if ($balance) {
            $balance->decrement('pending_days', $leaveRequest->total_days);
            $balance->increment('used_days', $leaveRequest->total_days);
        }

        $leaveRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewer_comment' => $validated['reviewer_comment'] ?? null,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorize('review', $leaveRequest);

        $validated = $request->validate([
            'reviewer_comment' => 'required|string|max:500',
        ]);

        $balance = $leaveRequest->user->getLeaveBalance($leaveRequest->leave_type_id);
        if ($balance) {
            $balance->decrement('pending_days', $leaveRequest->total_days);
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewer_comment' => $validated['reviewer_comment'],
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Leave request rejected.');
    }
}
