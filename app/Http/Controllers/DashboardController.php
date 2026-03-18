<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $year = now()->year;

        $balances = $user->leaveBalances()
            ->with('leaveType')
            ->where('year', $year)
            ->get();

        $recentRequests = $user->leaveRequests()
            ->with('leaveType')
            ->latest()
            ->take(5)
            ->get();

        $pendingCount = $user->leaveRequests()->where('status', 'pending')->count();

        $stats = [
            'pending' => $pendingCount,
            'approved_this_year' => $user->leaveRequests()
                ->where('status', 'approved')
                ->whereYear('start_date', $year)
                ->count(),
            'total_days_taken' => $user->leaveRequests()
                ->where('status', 'approved')
                ->whereYear('start_date', $year)
                ->sum('total_days'),
        ];

        $pendingApprovals = null;
        if ($user->isManager()) {
            $pendingApprovals = LeaveRequest::with(['user', 'leaveType'])
                ->where('status', 'pending')
                ->whereHas('user', function ($q) use ($user) {
                    if (!$user->isAdmin()) {
                        $q->where('manager_id', $user->id);
                    }
                })
                ->latest()
                ->take(5)
                ->get();
        }

        return view('dashboard', compact('balances', 'recentRequests', 'stats', 'pendingApprovals'));
    }
}
