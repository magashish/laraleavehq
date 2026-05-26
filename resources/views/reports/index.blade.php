<x-app-layout>
<style>
.report-wrap { overflow-x:auto;-webkit-overflow-scrolling:touch; }
.report-wrap table { min-width:600px; }
.report-wrap th, .report-wrap td { white-space:nowrap; }
.remaining-bar { display:inline-block;vertical-align:middle;margin-left:6px;background:#f0f0f0;border-radius:999px;height:4px;width:64px; }
.remaining-bar-fill { height:4px;border-radius:999px; }
</style>
<div class="page">
    <div class="page-header">
        <h2>Reports</h2>
        <p>Attendance and leave analytics</p>
    </div>

    {{-- Filter form --}}
    <div class="card" style="margin-bottom:20px;"
         x-data="{ report: '{{ $reportType }}' }">
        <form method="GET" action="{{ route('reports.index') }}" style="display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;">
            <div class="form-group" style="margin:0;min-width:200px;">
                <label class="form-label">Report type</label>
                <select name="report" class="form-select" x-model="report">
                    <option value="late">Late arrivals (after 09:00)</option>
                    <option value="leave_summary">Annual leave summary</option>
                </select>
            </div>

            {{-- Employee & date range — only for late arrivals --}}
            <div class="form-group" style="margin:0;min-width:180px;" x-show="report === 'late'">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">All employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;" x-show="report === 'late'">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-input" value="{{ $from }}"
                       :required="report === 'late'">
            </div>
            <div class="form-group" style="margin:0;" x-show="report === 'late'">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-input" value="{{ $to }}"
                       :required="report === 'late'">
            </div>

            {{-- Year — only for leave summary --}}
            <div class="form-group" style="margin:0;" x-show="report === 'leave_summary'">
                <label class="form-label">Year</label>
                <select name="year" class="form-select" style="width:auto;">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div style="padding-bottom:1px;">
                <button type="submit" class="btn btn-primary">Run report</button>
            </div>
        </form>
    </div>

    {{-- ── Late arrivals results ── --}}
    @if($reportType === 'late' && $results !== null)
        {{-- Summary strip --}}
        <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px;">
            <div class="stat-card" style="flex:1;min-width:140px;">
                <div class="stat-label">Late arrivals</div>
                <div class="stat-val" style="color:{{ $summary['total_late'] > 0 ? '#ef4444' : '#059669' }}">{{ $summary['total_late'] }}</div>
                <div class="stat-sub">{{ $summary['from'] }} – {{ $summary['to'] }}</div>
            </div>
            <div class="stat-card" style="flex:1;min-width:140px;">
                <div class="stat-label">Days recorded</div>
                <div class="stat-val">{{ $summary['total_days'] }}</div>
                <div class="stat-sub">with a sign-in</div>
            </div>
            <div class="stat-card" style="flex:1;min-width:140px;">
                <div class="stat-label">On-time rate</div>
                <div class="stat-val" style="color:#059669;">
                    {{ $summary['total_days'] > 0 ? round((($summary['total_days'] - $summary['total_late']) / $summary['total_days']) * 100) : 100 }}%
                </div>
                <div class="stat-sub">{{ $summary['employee'] }}</div>
            </div>
            <div class="stat-card" style="flex:1;min-width:140px;">
                <div class="stat-label">Total time late</div>
                <div class="stat-val" style="color:{{ $summary['total_minutes_late'] > 0 ? '#ef4444' : '#059669' }};">
                    @if($summary['total_hours_late'] > 0)
                        {{ $summary['total_hours_late'] }}h {{ $summary['remaining_mins_late'] }}m
                    @else
                        {{ $summary['remaining_mins_late'] }}m
                    @endif
                </div>
                <div class="stat-sub">combined late time</div>
            </div>
        </div>

        <div class="card">
            <div class="card-title" style="margin-bottom:12px;">
                Late arrivals — {{ $summary['employee'] }}
                <span style="font-size:12px;font-weight:400;color:#888;">{{ $summary['from'] }} to {{ $summary['to'] }}</span>
                <a href="{{ route('reports.export', ['report' => $reportType, 'employee_id' => $employeeId, 'from' => $from, 'to' => $to]) }}"
                   class="btn btn-outline btn-sm" style="margin-left:auto;">
                    &#8595; Export CSV
                </a>
            </div>

            @if($results->isEmpty())
                <div class="empty-state" style="padding:30px 0;">No late arrivals in this period.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Signed in</th>
                            <th>Minutes late</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $row)
                            <tr>
                                <td style="font-weight:500;">{{ $row['employee'] }}</td>
                                <td style="font-size:13px;">{{ $row['date'] }}</td>
                                <td>
                                    <span style="font-size:13px;font-weight:600;color:#ef4444;">{{ $row['signed_in_at'] }}</span>
                                </td>
                                <td>
                                    <span style="font-size:12px;padding:2px 8px;border-radius:99px;background:#fee2e2;color:#b91c1c;font-weight:500;">
                                        +{{ $row['minutes_late'] }} min
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    {{-- ── Annual leave summary results ── --}}
    @elseif($reportType === 'leave_summary' && $leaveData !== null)
        <div class="card">
            <div class="card-title" style="margin-bottom:12px;">
                Annual leave summary — {{ $year }}
                <a href="{{ route('reports.export', ['report' => 'leave_summary', 'year' => $year]) }}"
                   class="btn btn-outline btn-sm" style="margin-left:auto;">
                    &#8595; Export CSV
                </a>
            </div>

            @if($leaveData->isEmpty())
                <div class="empty-state" style="padding:30px 0;">No employees found.</div>
            @else
            <div class="report-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th style="text-align:center;">Allowed</th>
                        <th style="text-align:center;">Used</th>
                        <th style="text-align:center;">Remaining</th>
                        @foreach($leaveTypes as $lt)
                            <th style="text-align:center;">
                                <span style="padding:2px 8px;border-radius:999px;font-size:11px;font-weight:500;background:{{ $lt->color }}33;">
                                    {{ $lt->name }}
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaveData as $emp)
                        @php
                            $remaining   = $emp['annual_remaining'] === PHP_INT_MAX ? null : $emp['annual_remaining'];
                            $allowed     = $emp['days_allowed'];
                            $pct         = ($emp['has_allowance'] && $allowed > 0)
                                ? min(100, round(($emp['annual_used'] / $allowed) * 100)) : 0;
                            $remainColor = !$emp['has_allowance'] ? '#aaa'
                                : ($remaining <= 5 ? '#ef4444' : ($remaining <= 10 ? '#f97316' : '#059669'));
                            $barColor    = $pct >= 80 ? '#ef4444' : ($pct >= 60 ? '#f97316' : '#38bdf8');
                        @endphp
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    @if($emp['photo'])
                                        <img src="{{ $emp['photo'] }}" alt="{{ $emp['name'] }}"
                                             style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                                    @else
                                        <div class="avatar" style="width:28px;height:28px;font-size:10px;flex-shrink:0;background:{{ $emp['color'] ?? '#38bdf8' }}33;color:{{ $emp['color'] ?? '#38bdf8' }};">
                                            {{ $emp['initials'] }}
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight:500;font-size:13px;">{{ $emp['name'] }}</div>
                                        <div style="font-size:11px;color:#888;">{{ $emp['role_label'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align:center;font-size:13px;">
                                @if($emp['has_allowance']) {{ $emp['days_allowed'] }} @else <span style="color:#bbb;">—</span> @endif
                            </td>
                            <td style="text-align:center;font-size:13px;">
                                @if($emp['has_allowance']) {{ number_format($emp['annual_used'], 1) }} @else <span style="color:#bbb;">—</span> @endif
                            </td>
                            <td style="text-align:center;">
                                @if($emp['has_allowance'])
                                    <span style="font-size:13px;font-weight:600;color:{{ $remainColor }};">
                                        {{ number_format($remaining, 1) }}
                                    </span>
                                    <div class="remaining-bar">
                                        <div class="remaining-bar-fill" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
                                    </div>
                                @else
                                    <span style="color:#bbb;font-size:12px;">No allowance</span>
                                @endif
                            </td>
                            @foreach($leaveTypes as $lt)
                                @php $days = $emp['by_type'][$lt->id] ?? 0; @endphp
                                <td style="text-align:center;font-size:13px;">
                                    @if($days > 0)
                                        <span style="font-weight:500;">{{ number_format($days, 1) }}</span>
                                    @else
                                        <span style="color:#ddd;">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>

    @else
        <div class="card">
            <div class="empty-state" style="padding:40px 0;">Select options and run the report.</div>
        </div>
    @endif
</div>
</x-app-layout>
