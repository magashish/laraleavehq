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
                    $minutesLate = (int) Carbon::parse($c->date->toDateString() . ' 09:00:00')
                        ->diffInMinutes($c->checked_in_at);
                    return [
                        'employee'     => $c->user->name,
                        'date'         => $c->date->format('l, j M Y'),
                        'signed_in_at' => $c->checked_in_at->format('H:i'),
                        'minutes_late' => max(1, $minutesLate),
                    ];
                })->values();

                $totalMinutesLate = $results->sum('minutes_late');

                $summary = [
                    'total_late'          => $results->count(),
                    'total_days'          => $checkins->count(),
                    'total_minutes_late'  => $totalMinutesLate,
                    'total_hours_late'    => floor($totalMinutesLate / 60),
                    'remaining_mins_late' => $totalMinutesLate % 60,
                    'employee'            => $employeeId ? User::find($employeeId)?->name : 'All employees',
                    'from'                => Carbon::parse($from)->format('j M Y'),
                    'to'                  => Carbon::parse($to)->format('j M Y'),
                ];
            }
        }

        return view('reports.index', compact('employees', 'results', 'summary', 'employeeId', 'from', 'to', 'reportType'));
    }

    public function export(Request $request)
    {
        if (!Auth::user()->isManager()) abort(403);

        $employeeId = $request->get('employee_id');
        $from       = $request->get('from');
        $to         = $request->get('to');
        $reportType = $request->get('report', 'late');

        if (!$from || !$to) abort(400);

        $query = DailyCheckin::with('user')
            ->whereBetween('date', [$from, $to])
            ->whereNotNull('checked_in_at')
            ->orderBy('date')
            ->orderBy('checked_in_at');

        if ($employeeId) {
            $query->where('user_id', $employeeId);
        }

        $checkins = $query->get();
        $rows     = collect();

        if ($reportType === 'late') {
            $rows = $checkins->filter(fn($c) => $c->checked_in_at->format('H:i:s') > '09:00:00')
                ->map(function ($c) {
                    $minutesLate = (int) Carbon::parse($c->date->toDateString() . ' 09:00:00')
                        ->diffInMinutes($c->checked_in_at);
                    return [
                        'Employee'     => $c->user->name,
                        'Date'         => $c->date->format('l, j M Y'),
                        'Signed In'    => $c->checked_in_at->format('H:i'),
                        'Minutes Late' => max(1, $minutesLate),
                    ];
                })->values();
        }

        $filename = 'late-arrivals-' . $from . '-to-' . $to . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            if ($rows->isNotEmpty()) {
                fputcsv($handle, array_keys($rows->first()));
            }
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
