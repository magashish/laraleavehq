<x-app-layout>
<div class="page">
    <div class="page-header">
        <h2>Reports</h2>
        <p>Attendance and leave analytics</p>
    </div>

    {{-- Filter form --}}
    <div class="card" style="margin-bottom:20px;">
        <form method="GET" action="{{ route('reports.index') }}" style="display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;">
            <div class="form-group" style="margin:0;min-width:160px;">
                <label class="form-label">Report type</label>
                <select name="report" class="form-select">
                    <option value="late" {{ $reportType === 'late' ? 'selected' : '' }}>Late arrivals (after 09:00)</option>
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:180px;">
                <label class="form-label">Employee</label>
                <select name="employee_id" class="form-select">
                    <option value="">All employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-input" value="{{ $from }}" required>
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-input" value="{{ $to }}" required>
            </div>
            <div style="padding-bottom:1px;">
                <button type="submit" class="btn btn-primary">Run report</button>
            </div>
        </form>
    </div>

    {{-- Results --}}
    @if($results !== null)
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
    @else
        <div class="card">
            <div class="empty-state" style="padding:40px 0;">Select a date range and run the report.</div>
        </div>
    @endif
</div>
</x-app-layout>
