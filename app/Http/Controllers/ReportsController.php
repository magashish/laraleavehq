<?php

namespace App\Http\Controllers;

use App\Models\DailyCheckin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isManager()) abort(403);

        $employees = User::orderBy('name')->get();
        $results   = null;
        $summary   = null;

        $employeeId = $request->get('employee_id');
        $from       = $request->get('from');
        $to         = $request->get('to');
        $reportType = $request->get('report', 'late');

        if ($from && $to) {
            $query = DailyCheckin::with('user')
                ->whereBetween('date', [$from, $to])
                ->whereNotNull('checked_in_at')
                ->orderBy('date')
                ->orderBy('checked_in_at');

            if ($employeeId) {
                $query->where('user_id', $employeeId);
            }

            $checkins = $query->get();

            if ($reportType === 'late') {
                $lateThreshold = '09:00:00';

                $results = $checkins->filter(function ($c) use ($lateThreshold) {
                    return $c->checked_in_at->format('H:i:s') > $lateThreshold;
                })->map(function ($c) {
                    $minutesLate = $c->checked_in_at->diffInMinutes(
                        Carbon::parse($c->date->toDateString() . ' 09:00:00')
                    );
                    return [
                        'employee'     => $c->user->name,
                        'date'         => $c->date->format('l, j M Y'),
                        'signed_in_at' => $c->checked_in_at->format('H:i'),
                        'minutes_late' => $minutesLate,
                    ];
                })->values();

                $summary = [
                    'total_late'   => $results->count(),
                    'total_days'   => $checkins->count(),
                    'employee'     => $employeeId ? User::find($employeeId)?->name : 'All employees',
                    'from'         => Carbon::parse($from)->format('j M Y'),
                    'to'           => Carbon::parse($to)->format('j M Y'),
                ];
            }
        }

        return view('reports.index', compact('employees', 'results', 'summary', 'employeeId', 'from', 'to', 'reportType'));
    }
}
