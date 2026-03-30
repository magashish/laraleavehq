<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;

class CalendarController extends Controller
{
    public function feed(string $token): Response
    {
        $user = User::where('calendar_token', $token)->firstOrFail();

        $leaves = $user->leaveRequests()
            ->with('leaveType')
            ->where('status', 'approved')
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//LeaveHQ//LeaveHQ//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . $this->esc($user->name) . ' - Leave',
            'X-WR-CALDESC:Leave calendar for ' . $this->esc($user->name),
            'X-WR-TIMEZONE:UTC',
            'REFRESH-INTERVAL;VALUE=DURATION:PT1H',
            'X-PUBLISHED-TTL:PT1H',
        ];

        foreach ($leaves as $leave) {
            $typeName = $leave->leaveType?->name ?? 'Leave';
            $summary  = $typeName;

            if ($leave->is_half_day) {
                $summary .= ' (Half day - ' . ucfirst($leave->half_day_part) . ')';
            }

            $dtStart = $leave->start_date->format('Ymd');
            // iCal all-day DTEND is exclusive (day after the last day)
            $dtEnd   = $leave->end_date->addDay()->format('Ymd');

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:leavehq-leave-' . $leave->id . '@leavehq';
            $lines[] = 'DTSTAMP:' . now()->format('Ymd\THis\Z');
            $lines[] = 'DTSTART;VALUE=DATE:' . $dtStart;
            $lines[] = 'DTEND;VALUE=DATE:' . $dtEnd;
            $lines[] = 'SUMMARY:' . $this->esc($summary);
            $lines[] = 'STATUS:CONFIRMED';

            if ($leave->reason) {
                $lines[] = 'DESCRIPTION:' . $this->esc($leave->reason);
            }

            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        $ical = implode("\r\n", $lines) . "\r\n";

        return response($ical, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="leavehq.ics"',
            'Cache-Control'       => 'no-cache, must-revalidate',
        ]);
    }

    private function esc(string $value): string
    {
        return str_replace(["\r\n", "\n", "\r", ',', ';', '\\'], ['\\n', '\\n', '\\n', '\\,', '\\;', '\\\\'], $value);
    }
}
