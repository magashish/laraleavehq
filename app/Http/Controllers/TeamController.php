<?php

namespace App\Http\Controllers;

use App\Models\BankHoliday;
use App\Models\LeaveRequest;
use App\Models\TeamNotice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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

        $monthDays    = now()->daysInMonth;
        $monthDayInfo = collect(range(1, $monthDays))->map(function ($day) {
            $d = now()->startOfMonth()->addDays($day - 1);
            return [
                'num'     => $day,
                'label'   => $d->format('D')[0],
                'weekend' => $d->isWeekend(),
            ];
        })->values()->toArray();

        $publicHolidays = BankHoliday::whereBetween('date', [$monthStart, $monthEnd])
            ->pluck('date')
            ->map(fn($d) => $d->toDateString())
            ->toArray();

        $employees = User::with([
            'leaveRequests' => fn($q) => $q
                ->with('leaveType')
                ->where('status', 'approved')
                ->where('start_date', '<=', $monthEnd)
                ->where('end_date', '>=', $monthStart),
            'checkins' => fn($q) => $q->whereIn('date', array_merge([$today], $weekDates)),
        ])->orderBy('name')->get();

        $dayOfWeek = now()->dayOfWeekIso; // 1=Mon … 7=Sun
        $todayIdx  = ($dayOfWeek >= 1 && $dayOfWeek <= 5) ? $dayOfWeek - 1 : null;

        $teamData = $employees->map(function ($emp) use ($today, $weekDates, $monthStart, $monthEnd, $publicHolidays) {
            $todayCheckin = $emp->checkins->first(fn($c) => $c->date->toDateString() === $today);
            $signedIn     = $todayCheckin && $todayCheckin->checked_in_at && !$todayCheckin->signed_out_at;
            return [
                'id'         => $emp->id,
                'name'       => $emp->name,
                'role'       => $emp->role,
                'color'      => $emp->color,
                'initials'   => $emp->initials(),
                'photo_url'  => $emp->photoUrl(),
                'location'   => $emp->work_location,
                'status'     => $this->getUserStatus($emp, $today, $publicHolidays),
                'signed_in'  => $signedIn,
                'time'       => $todayCheckin?->checked_in_at?->format('H:i') ?? '—',
                'week'       => array_map(fn($d) => $this->getWeekDayStatus($emp, $d, $today, $publicHolidays), $weekDates),
                'month'      => $this->getMonthStats($emp, $monthStart, $monthEnd, $publicHolidays),
                'monthGrid'  => $this->getMonthDayStatuses($emp, $monthStart, $monthEnd, $publicHolidays),
            ];
        })->values();

        $notices        = $this->buildNotices($employees, $today);
        $weekLabels     = array_map(fn($d) => Carbon::parse($d)->format('D'), $weekDates);
        $managerNotices = TeamNotice::with(['author', 'targetUser'])->latest()->get();
        $allEmployees   = $employees;

        return view('team.index', compact('teamData', 'notices', 'todayIdx', 'weekLabels', 'managerNotices', 'allEmployees', 'monthDayInfo'));
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

        $publicHolidays = BankHoliday::whereBetween('date', [$from, $to])
            ->pluck('date')
            ->map(fn($d) => $d->toDateString())
            ->toArray();

        $employees = User::with([
            'leaveRequests' => fn($q) => $q
                ->with('leaveType')
                ->where('status', 'approved')
                ->where('start_date', '<=', $to)
                ->where('end_date', '>=', $from),
        ])->orderBy('name')->get();

        $teamData = $employees->map(function ($emp) use ($from, $to, $publicHolidays) {
            $leave = 0;
            $sick  = 0;

            foreach ($emp->leaveRequests as $l) {
                $start  = max($l->start_date->toDateString(), $from);
                $end    = min($l->end_date->toDateString(), $to);
                $isSick = str_contains(strtolower($l->leaveType?->name ?? ''), 'sick');

                $d = Carbon::parse($start);
                $e = Carbon::parse($end);
                while ($d->lte($e)) {
                    if (!$d->isWeekend() && !in_array($d->toDateString(), $publicHolidays)) {
                        $isSick ? $sick++ : $leave++;
                    }
                    $d->addDay();
                }
            }

            $workingDays = $this->countWorkingDays($from, min($to, now()->toDateString()), $publicHolidays);
            $nonLeave    = max(0, $workingDays - $leave - $sick);
            $office      = $emp->work_location === 'office' ? $nonLeave : 0;
            $remote      = $emp->work_location === 'remote' ? $nonLeave : 0;

            return [
                'id'       => $emp->id,
                'name'     => $emp->name,
                'role'     => $emp->role,
                'color'    => $emp->color,
                'initials' => $emp->initials(),
                'photo_url'=> $emp->photoUrl(),
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

    public function storeNotice(Request $request)
    {
        if (!Auth::user()->isManager()) abort(403);

        $validated = $request->validate([
            'message'        => 'required|string|max:500',
            'target_user_id' => 'nullable|exists:users,id',
        ]);

        TeamNotice::create([
            'created_by'     => Auth::id(),
            'target_user_id' => $validated['target_user_id'] ?? null,
            'message'        => $validated['message'],
        ]);

        return back()->with('success', 'Notice posted.');
    }

    public function destroyNotice(TeamNotice $notice)
    {
        if (!Auth::user()->isManager()) abort(403);
        $notice->delete();
        return back();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Status for weekly grid: uses actual check-in for past days,
     * falls back to work_location only for today and future days.
     */
    private function getWeekDayStatus(User $emp, string $date, string $today, array $publicHolidays = []): string
    {
        // Leave/holiday always takes priority
        $status = $this->getUserStatus($emp, $date, $publicHolidays);
        if (in_array($status, ['leave', 'sick', 'holiday', 'medical', 'medical-morning', 'medical-afternoon'])) {
            return $status;
        }

        // For past days, use actual check-in if available; otherwise unknown
        if ($date < $today) {
            $checkin = $emp->checkins->first(fn($c) => $c->date->toDateString() === $date);
            return $checkin?->status ?? 'unknown';
        }

        // Today and future: use work_location (planned)
        return $status;
    }

    private function getUserStatus(User $emp, string $date, array $publicHolidays = []): string
    {
        if (in_array($date, $publicHolidays)) {
            return 'holiday';
        }

        $leave = $emp->leaveRequests->first(
            fn($l) => $l->start_date->toDateString() <= $date && $l->end_date->toDateString() >= $date
        );

        if ($leave) {
            if ($leave->is_short_leave) {
                $part = $leave->short_leave_part;
                return $part ? "medical-{$part}" : 'medical';
            }
            return str_contains(strtolower($leave->leaveType?->name ?? ''), 'sick') ? 'sick' : 'leave';
        }

        return $emp->work_location ?? 'unknown';
    }

    private function getWeekDates(): array
    {
        $monday = now()->startOfWeek(Carbon::MONDAY);

        return array_map(fn($i) => $monday->copy()->addDays($i)->toDateString(), range(0, 4));
    }

    private function countWorkingDays(string $from, string $to, array $publicHolidays = []): int
    {
        $count = 0;
        $d = Carbon::parse($from);
        $e = Carbon::parse($to);
        while ($d->lte($e)) {
            if (!$d->isWeekend() && !in_array($d->toDateString(), $publicHolidays)) {
                $count++;
            }
            $d->addDay();
        }
        return $count;
    }

    private function getMonthDayStatuses(User $emp, string $monthStart, string $monthEnd, array $publicHolidays = []): array
    {
        $statuses = [];
        $today    = now()->toDateString();
        $d        = Carbon::parse($monthStart);
        $end      = Carbon::parse($monthEnd);

        while ($d->lte($end)) {
            $dateStr = $d->toDateString();
            if ($d->isWeekend()) {
                $statuses[] = 'weekend';
            } else {
                $status = $this->getUserStatus($emp, $dateStr, $publicHolidays);
                // Future days: show leave/holiday/medical if booked, otherwise blank
                if ($dateStr > $today && !in_array($status, ['leave', 'sick', 'holiday', 'medical', 'medical-morning', 'medical-afternoon'])) {
                    $statuses[] = 'unknown';
                } else {
                    $statuses[] = $status;
                }
            }
            $d->addDay();
        }

        return $statuses;
    }

    private function getMonthStats(User $emp, string $monthStart, string $monthEnd, array $publicHolidays = []): array
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
                if (!$d->isWeekend() && !in_array($d->toDateString(), $publicHolidays)) {
                    $isSick ? $sick++ : $leave++;
                }
                $d->addDay();
            }
        }

        $workingDays = $this->countWorkingDays($monthStart, min($monthEnd, now()->toDateString()), $publicHolidays);
        $nonLeave    = max(0, $workingDays - $leave - $sick);
        $office      = $emp->work_location === 'office' ? $nonLeave : 0;
        $remote      = $emp->work_location === 'remote' ? $nonLeave : 0;

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
