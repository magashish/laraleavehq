<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isManager()) {
            abort(403);
        }

        $today      = now()->toDateString();
        $weekDates  = $this->getWeekDates();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd   = now()->endOfMonth()->toDateString();

        $employees = User::with([
            'leaveRequests' => fn($q) => $q
                ->with('leaveType')
                ->where('status', 'approved')
                ->where('start_date', '<=', $monthEnd)
                ->where('end_date', '>=', $monthStart),
            'checkins' => fn($q) => $q
                ->where('date', '>=', $monthStart)
                ->where('date', '<=', $monthEnd),
        ])->orderBy('name')->get();

        $dayOfWeek = now()->dayOfWeekIso; // 1=Mon … 7=Sun
        $todayIdx  = ($dayOfWeek >= 1 && $dayOfWeek <= 5) ? $dayOfWeek - 1 : null;

        $teamData = $employees->map(function ($emp) use ($today, $weekDates, $monthStart, $monthEnd) {
            $todayCheckin = $emp->checkins->first(fn($c) => $c->date->toDateString() === $today);

            return [
                'id'       => $emp->id,
                'name'     => $emp->name,
                'role'     => $emp->role,
                'color'    => $emp->color,
                'initials' => $emp->initials(),
                'status'   => $this->getUserStatus($emp, $today),
                'time'     => $todayCheckin?->checked_in_at?->format('H:i') ?? '—',
                'week'     => array_map(fn($d) => $this->getUserStatus($emp, $d), $weekDates),
                'month'    => $this->getMonthStats($emp, $monthStart, $monthEnd),
            ];
        })->values();

        $notices    = $this->buildNotices($employees, $today);
        $weekLabels = array_map(fn($d) => Carbon::parse($d)->format('D'), $weekDates);

        return view('team.index', compact('teamData', 'notices', 'todayIdx', 'weekLabels'));
    }

    public function custom(Request $request)
    {
        if (!Auth::user()->isManager()) abort(403);

        $validated = $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $from = $validated['from'];
        $to   = $validated['to'];

        $employees = User::with([
            'leaveRequests' => fn($q) => $q
                ->with('leaveType')
                ->where('status', 'approved')
                ->where('start_date', '<=', $to)
                ->where('end_date', '>=', $from),
            'checkins' => fn($q) => $q
                ->where('date', '>=', $from)
                ->where('date', '<=', $to),
        ])->orderBy('name')->get();

        $teamData = $employees->map(function ($emp) use ($from, $to) {
            $leave = 0;
            $sick  = 0;

            foreach ($emp->leaveRequests as $l) {
                $start  = max($l->start_date->toDateString(), $from);
                $end    = min($l->end_date->toDateString(), $to);
                $isSick = str_contains(strtolower($l->leaveType?->name ?? ''), 'sick');

                $d = Carbon::parse($start);
                $e = Carbon::parse($end);
                while ($d->lte($e)) {
                    if (!$d->isWeekend()) { $isSick ? $sick++ : $leave++; }
                    $d->addDay();
                }
            }

            $office = $emp->checkins->filter(fn($c) => $c->status === 'office')->count();
            $remote = $emp->checkins->filter(fn($c) => $c->status === 'remote')->count();

            return [
                'id'       => $emp->id,
                'name'     => $emp->name,
                'role'     => $emp->role,
                'color'    => $emp->color,
                'initials' => $emp->initials(),
                'office'   => $office,
                'remote'   => $remote,
                'leave'    => $leave,
                'sick'     => $sick,
            ];
        })->values();

        $totals = [
            'office' => $teamData->sum('office'),
            'remote' => $teamData->sum('remote'),
            'leave'  => $teamData->sum('leave'),
            'sick'   => $teamData->sum('sick'),
        ];

        return response()->json(compact('teamData', 'totals'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getUserStatus(User $emp, string $date): string
    {
        $leave = $emp->leaveRequests->first(
            fn($l) => $l->start_date->toDateString() <= $date && $l->end_date->toDateString() >= $date
        );

        if ($leave) {
            return str_contains(strtolower($leave->leaveType?->name ?? ''), 'sick') ? 'sick' : 'leave';
        }

        $checkin = $emp->checkins->first(fn($c) => $c->date->toDateString() === $date);

        return $checkin?->status ?? 'unknown';
    }

    private function getWeekDates(): array
    {
        $monday = now()->startOfWeek(Carbon::MONDAY);

        return array_map(fn($i) => $monday->copy()->addDays($i)->toDateString(), range(0, 4));
    }

    private function getMonthStats(User $emp, string $monthStart, string $monthEnd): array
    {
        $leave = 0;
        $sick  = 0;

        foreach ($emp->leaveRequests as $l) {
            $start  = max($l->start_date->toDateString(), $monthStart);
            $end    = min($l->end_date->toDateString(), $monthEnd);
            $isSick = str_contains(strtolower($l->leaveType?->name ?? ''), 'sick');

            $d = Carbon::parse($start);
            $e = Carbon::parse($end);
            while ($d->lte($e)) {
                if (!$d->isWeekend()) {
                    $isSick ? $sick++ : $leave++;
                }
                $d->addDay();
            }
        }

        $office = $emp->checkins->filter(fn($c) => $c->status === 'office')->count();
        $remote = $emp->checkins->filter(fn($c) => $c->status === 'remote')->count();

        return compact('office', 'remote', 'leave', 'sick');
    }

    private function buildNotices(Collection $employees, string $today): array
    {
        $notices = [];

        foreach ($employees as $emp) {
            $leave = $emp->leaveRequests->first(
                fn($l) => $l->start_date->toDateString() <= $today && $l->end_date->toDateString() >= $today
            );

            if (!$leave) continue;

            $isSick = str_contains(strtolower($leave->leaveType?->name ?? ''), 'sick');

            if ($isSick) {
                $daysIn    = max(1, Carbon::parse($leave->start_date)->diffInWeekdays(now()) + 1);
                $notices[] = ['type' => 'warn', 'title' => "{$emp->name} on sick leave", 'meta' => "Day {$daysIn} of absence"];
            } else {
                $returns   = Carbon::parse($leave->end_date)->addWeekday()->format('D j M');
                $typeName  = $leave->leaveType?->name ?? 'leave';
                $notices[] = ['type' => 'warn', 'title' => "{$emp->name} on {$typeName}", 'meta' => "Returns {$returns}"];
            }
        }

        $pendingCount = LeaveRequest::where('status', 'pending')->count();
        if ($pendingCount > 0) {
            $notices[] = [
                'type'  => 'info',
                'title' => "{$pendingCount} pending leave request" . ($pendingCount > 1 ? 's' : ''),
                'meta'  => 'Awaiting your approval',
            ];
        }

        return $notices;
    }
}
