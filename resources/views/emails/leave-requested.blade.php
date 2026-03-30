<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .header { background: #ffffff; text-align: center; padding: 36px 24px 20px; }
        .icon-circle { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 50%; background: #fef3c7; margin-bottom: 12px; }
        .icon-circle svg { width: 32px; height: 32px; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 700; color: #d97706; }
        .greeting { padding: 0 32px 20px; color: #374151; font-size: 15px; line-height: 1.6; }
        .card { margin: 0 24px 24px; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
        .card-header { display: flex; align-items: center; gap: 10px; padding: 16px 20px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        .card-header span { font-size: 16px; font-weight: 600; color: #111827; }
        .card-body { padding: 20px; display: flex; align-items: center; gap: 20px; }
        .date-box { min-width: 72px; text-align: center; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
        .date-box .month { background: #e5e7eb; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #6b7280; padding: 4px 0; }
        .date-box .day { font-size: 28px; font-weight: 700; color: #111827; padding: 8px 0; }
        .date-info { color: #374151; font-size: 14px; line-height: 1.8; }
        .date-info .date-label { font-weight: 600; color: #111827; font-size: 15px; }
        .date-info .duration { color: #6b7280; font-size: 13px; }
        .detail-row { padding: 0 20px 16px; display: flex; gap: 8px; }
        .detail-row .label { color: #9ca3af; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; min-width: 80px; padding-top: 1px; }
        .detail-row .value { color: #374151; font-size: 13px; }
        .cta { text-align: center; padding: 8px 24px 32px; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-size: 14px; font-weight: 600; }
        .footer { text-align: center; padding: 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 12px; }
        .footer strong { color: #6b7280; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="icon-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <h1>Leave Requested</h1>
    </div>

    <div class="greeting">
        <p>Hi there,</p>
        <p><strong>{{ $leave->employee->name }}</strong> has submitted a leave request that requires your attention.</p>
    </div>

    <div class="card">
        <div class="card-header">
            <span>&#127965;</span>
            <span>{{ $leave->leaveType?->name ?? 'Holiday' }}</span>
        </div>
        <div class="card-body">
            <div class="date-box">
                <div class="month">{{ $leave->start_date->format('M') }}</div>
                <div class="day">{{ $leave->start_date->format('j') }}</div>
            </div>
            <div class="date-info">
                @if($leave->is_half_day)
                    <div class="date-label">{{ $leave->start_date->format('l, j F Y') }}</div>
                    <div class="duration">Half day &mdash; {{ ucfirst($leave->half_day_part) }}</div>
                @elseif($leave->start_date->eq($leave->end_date))
                    <div class="date-label">{{ $leave->start_date->format('l, j F Y') }}</div>
                    <div class="duration">1 day</div>
                @else
                    <div class="date-label">{{ $leave->start_date->format('j M') }} &mdash; {{ $leave->end_date->format('j M Y') }}</div>
                    <div class="duration">{{ $leave->days }} working day(s)</div>
                @endif
            </div>
        </div>

        @if($leave->reason)
        <div class="detail-row">
            <span class="label">Notes</span>
            <span class="value">{{ $leave->reason }}</span>
        </div>
        @endif
    </div>

    <div class="cta">
        <a href="{{ url('/leave') }}" class="btn">Review Request</a>
    </div>

    <div class="footer">
        <strong>LeaveHQ</strong> &mdash; Leave management made simple
    </div>
</div>
</body>
</html>
