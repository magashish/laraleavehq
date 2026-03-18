<?php

namespace App\Http\Controllers;

use App\Models\BankHoliday;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $leaves = $user->leaveRequests()->get();
        $bankHolidays = BankHoliday::orderBy('date')->get();
        $bankHolidayDates = $bankHolidays->pluck('date')->map(fn($d) => $d->toDateString())->toArray();

        $usedDays = $leaves->where('status', 'approved')->sum('days');
        $daysRemaining = $user->days_allowed - $usedDays;

        $upcomingLeaves = $leaves
            ->where('start_date', '>=', now()->startOfDay())
            ->sortBy('start_date')
            ->take(4)
            ->values();

        $pendingApprovalCount = null;
        $offToday = null;
        if ($user->is_manager) {
            $pendingApprovalCount = LeaveRequest::where('status', 'pending')->count();
            $offToday = LeaveRequest::with('employee')
                ->where('status', 'approved')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get()
                ->map(fn($l) => $l->employee)
                ->unique('id')
                ->values();
        }

        return view('dashboard', compact(
            'user', 'leaves', 'bankHolidayDates', 'usedDays', 'daysRemaining',
            'upcomingLeaves', 'pendingApprovalCount', 'offToday'
        ));
    }
}
