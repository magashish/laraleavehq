<x-app-layout>
<div class="page" x-data="calendar()" x-init="init()">
    <div class="page-header">
        <h2>Dashboard</h2>
        <p>Welcome back, {{ Auth::user()->name }}</p>
    </div>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Days Remaining</div>
            @if(Auth::user()->hasHolidayAllowance())
                <div class="stat-val" style="color:{{ $daysRemaining < 5 ? '#ef4444' : '#059669' }}">{{ $daysRemaining }}</div>
                <div class="stat-sub">of {{ Auth::user()->days_allowed }} allowed</div>
            @else
                <div class="stat-val" style="color:#aaa;">N/A</div>
                <div class="stat-sub">{{ Auth::user()->roleBadgeLabel() }}</div>
            @endif
        </div>
        <div class="stat-card">
            <div class="stat-label">Days Used</div>
            <div class="stat-val">{{ $usedDays }}</div>
            <div class="stat-sub">approved leave</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Days Allowed</div>
            <div class="stat-val">{{ Auth::user()->days_allowed }}</div>
            <div class="stat-sub">annual entitlement</div>
        </div>
        <div class="stat-card">
            @if(Auth::user()->isManager())
                <div class="stat-label">Pending Approval</div>
                <div class="stat-val" style="color:{{ $pendingApprovalCount > 0 ? '#d97706' : '#1a1a1a' }}">{{ $pendingApprovalCount }}</div>
                <div class="stat-sub">requests awaiting</div>
            @else
                <div class="stat-label">Requests This Year</div>
                <div class="stat-val">{{ $leaves->count() }}</div>
                <div class="stat-sub">total submitted</div>
            @endif
        </div>
    </div>

    {{-- ── Work location (set by admin, read-only for employees) ── --}}
    @if(!$user->isManager())
    <div class="card" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div>
                <div class="card-title" style="margin-bottom:2px;">My work location</div>
                <div style="font-size:12px;color:#888;">Set by your manager</div>
            </div>
            @if($user->work_location)
                <span style="font-size:13px;color:#555;">
                    You're working
                    <strong style="color:{{ $user->work_location === 'office' ? '#1558a0' : '#0d6648' }}">
                        {{ $user->work_location === 'office' ? 'In office' : 'Remotely' }}
                    </strong>
                </span>
            @else
                <span style="font-size:13px;color:#aaa;">Your location hasn't been set yet.</span>
            @endif
        </div>
    </div>
    @endif

    <div class="two-col">
        {{-- Calendar --}}
        <div class="card">
            <div class="cal-nav">
                <span style="font-size:14px;font-weight:600;" x-text="monthName + ' ' + year"></span>
                <div style="display:flex;gap:6px;">
                    <button class="cal-nav-btn" @click="prevMonth()">&#8249;</button>
                    <button class="cal-nav-btn" @click="nextMonth()">&#8250;</button>
                </div>
            </div>

            <div class="calendar-grid">
                <template x-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']">
                    <div class="cal-header" x-text="d"></div>
                </template>
                <template x-for="(day, idx) in calDays" :key="year + '-' + month + '-' + idx">
                    <div class="cal-day"
                         :class="{
                             'cal-empty': !day,
                             'weekend': day && (idx % 7 === 0 || idx % 7 === 6),
                             'cal-past': day && isPast(day),
                             'today': day && isToday(day),
                             'bank-holiday': day && isBankHoliday(day),
                             'has-leave': day && hasApprovedLeave(day),
                             'pending-leave': day && hasPendingLeave(day),
                         }"
                         x-text="day || ''">
                    </div>
                </template>
            </div>

            <div class="legend">
                <div class="legend-item"><div class="legend-dot" style="background:#dcfce7;border:1px solid #86efac;"></div> Approved</div>
                <div class="legend-item"><div class="legend-dot" style="background:#fef9c3;border:1px solid #fde047;"></div> Pending</div>
                <div class="legend-item"><div class="legend-dot" style="border:2px solid #c4b5fd;"></div> Public holiday</div>
            </div>
        </div>

        <div>
            {{-- Upcoming leave --}}
            <div class="card">
                <div class="card-title">My upcoming leave</div>
                @forelse($upcomingLeaves as $leave)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f0ede8;">
                        <div>
                            <div style="font-size:13px;font-weight:500;">
                                {{ $leave->start_date->format('d M') }} &ndash; {{ $leave->end_date->format('d M Y') }}
                            </div>
                            <div style="font-size:12px;color:#888;">{{ $leave->days }} day(s) &bull; {{ $leave->reason }}</div>
                        </div>
                        <span class="badge badge-{{ $leave->status }}">{{ $leave->status }}</span>
                    </div>
                @empty
                    <div class="empty-state" style="padding:20px 0;">No upcoming leave.</div>
                @endforelse
            </div>

            @if(Auth::user()->isManager() && $offToday && $offToday->isNotEmpty())
                <div class="card">
                    <div class="card-title">Off today ({{ $offToday->count() }})</div>
                    @foreach($offToday as $emp)
                        <div style="display:flex;align-items:center;gap:8px;padding:6px 0;">
                            @if($emp->profile_photo)
                                <img src="{{ $emp->photoUrl() }}" alt="{{ $emp->name }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                            @else
                                <div class="avatar" style="width:28px;height:28px;font-size:11px;background:{{ $emp->color }}33;color:{{ $emp->color }}">
                                    {{ $emp->initials() }}
                                </div>
                            @endif
                            <div>
                                <div style="font-size:13px;font-weight:500;">{{ $emp->name }}</div>
                                <div style="font-size:11px;color:#888;">{{ $emp->role }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function calendar() {
    const leaves = @json($leaves->map(fn($l) => ['start' => $l->start_date->toDateString(), 'end' => $l->end_date->toDateString(), 'status' => $l->status]));
    const bankHolidays = @json($bankHolidayDates);
    const today = new Date();

    return {
        year: today.getFullYear(),
        month: today.getMonth(),
        calDays: [],
        get monthName() {
            return new Date(this.year, this.month, 1).toLocaleString('en-GB', { month: 'long' });
        },
        init() { this.buildCalendar(); },
        buildCalendar() {
            const first = new Date(this.year, this.month, 1).getDay();
            const daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
            const days = [];
            for (let i = 0; i < first; i++) days.push(null);
            for (let d = 1; d <= daysInMonth; d++) days.push(d);
            this.calDays = days;
        },
        prevMonth() {
            if (this.month === 0) { this.month = 11; this.year--; }
            else this.month--;
            this.buildCalendar();
        },
        nextMonth() {
            if (this.month === 11) { this.month = 0; this.year++; }
            else this.month++;
            this.buildCalendar();
        },
        dateStr(d) {
            return `${this.year}-${String(this.month + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        },
        localDate(d) {
            return new Date(this.year, this.month, d);
        },
        isPast(d) {
            const t = new Date(); t.setHours(0,0,0,0);
            return this.localDate(d) < t;
        },
        isToday(d) {
            const t = new Date(); t.setHours(0,0,0,0);
            return this.localDate(d).getTime() === t.getTime();
        },
        isBankHoliday(d) { return bankHolidays.includes(this.dateStr(d)); },
        hasApprovedLeave(d) {
            const ds = this.dateStr(d);
            if (bankHolidays.includes(ds)) return false;
            const dow = this.localDate(d).getDay();
            if (dow === 0 || dow === 6) return false;
            return leaves.some(l => l.status === 'approved' && l.start <= ds && l.end >= ds);
        },
        hasPendingLeave(d) {
            const ds = this.dateStr(d);
            if (bankHolidays.includes(ds)) return false;
            const dow = this.localDate(d).getDay();
            if (dow === 0 || dow === 6) return false;
            return leaves.some(l => l.status === 'pending' && l.start <= ds && l.end >= ds);
        },
    };
}
</script>
</x-app-layout>
