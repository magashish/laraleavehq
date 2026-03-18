<x-app-layout>
<div class="page">
    <div class="page-header">
        <h2>Team</h2>
        <p>Overview of your team's leave</p>
    </div>

    {{-- Leave Summary --}}
    <div class="card">
        <div class="card-title">Leave summary</div>
        @foreach($employees as $emp)
            @php
                $used = $emp->used_days ?? 0;
                $pct = $emp->days_allowed > 0 ? min(100, ($used / $emp->days_allowed) * 100) : 0;
                $barColor = $pct > 80 ? '#ef4444' : ($pct > 60 ? '#f59e0b' : '#1c2b3a');
                $remaining = $emp->days_allowed - $used;
            @endphp
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f0ede8;">
                <div class="avatar" style="width:34px;height:34px;font-size:12px;background:{{ $emp->color }}33;color:{{ $emp->color }};flex-shrink:0;">
                    {{ $emp->initials() }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <div>
                            <span style="font-size:13px;font-weight:500;">{{ $emp->name }}</span>
                            <span style="font-size:11px;color:#888;margin-left:6px;">{{ $emp->role }}</span>
                            @if($emp->is_manager)
                                <span class="badge badge-manager" style="margin-left:6px;">Manager</span>
                            @endif
                        </div>
                        <span style="font-size:12px;color:#666;">{{ $used }} / {{ $emp->days_allowed }} days used</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="leave-bar-track">
                            <div class="leave-bar-fill" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
                        </div>
                        <span style="font-size:12px;font-weight:600;color:{{ $remaining < 5 ? '#ef4444' : '#059669' }};white-space:nowrap;">
                            {{ $remaining }} left
                        </span>
                        @if($emp->pending_count > 0)
                            <span style="font-size:11px;color:#d97706;white-space:nowrap;">{{ $emp->pending_count }} pending</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Upcoming approved leave --}}
    <div class="card">
        <div class="card-title">Upcoming approved leave</div>
        @if($upcomingLeaves->isEmpty())
            <div class="empty-state">No upcoming approved leave.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Days</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($upcomingLeaves as $leave)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="avatar" style="width:26px;height:26px;font-size:10px;background:{{ $leave->employee->color }}33;color:{{ $leave->employee->color }}">
                                        {{ $leave->employee->initials() }}
                                    </div>
                                    <span style="font-size:13px;font-weight:500;">{{ $leave->employee->name }}</span>
                                </div>
                            </td>
                            <td style="font-size:13px;">{{ $leave->start_date->format('d M Y') }}</td>
                            <td style="font-size:13px;">{{ $leave->end_date->format('d M Y') }}</td>
                            <td><strong>{{ $leave->days }}</strong></td>
                            <td style="color:#555;font-size:13px;">{{ $leave->reason }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
</x-app-layout>
