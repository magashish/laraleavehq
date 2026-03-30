<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        if (!Auth::user()->is_manager) {
            abort(403);
        }

        $employees = User::withCount([
            'leaveRequests as used_days' => fn($q) => $q
                ->where('status', 'approved')
                ->where(function ($sq) {
                    $sq->whereNull('leave_type_id')
                       ->orWhereHas('leaveType', fn($ltq) => $ltq->where('counts_toward_allowance', true));
                })
                ->select(\DB::raw('sum(days)')),
            'leaveRequests as pending_count' => fn($q) => $q->where('status', 'pending'),
        ])->get();

        $upcomingLeaves = LeaveRequest::with('employee')
            ->where('status', 'approved')
            ->where('start_date', '>=', now()->startOfDay())
            ->orderBy('start_date')
            ->get();

        return view('team.index', compact('employees', 'upcomingLeaves'));
    }
}
