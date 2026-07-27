<x-app-layout>
<style>
.icon-btn { display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:6px;border:1px solid #e0e0e0;background:#f5f5f3;cursor:pointer;flex-shrink:0;transition:background .15s; }
.icon-btn:hover { background:#ebebeb; }
.icon-btn.approve { border-color:#6ee7b7;background:#f0fdf4; }
.icon-btn.approve:hover { background:#dcfce7; }
.icon-btn.reject  { border-color:#fca5a5;background:#fff5f5; }
.icon-btn.reject:hover  { background:#fee2e2; }
.icon-btn.danger  { border-color:#e0e0e0;background:#fff; }
.icon-btn.danger:hover  { background:#fee2e2;border-color:#fca5a5; }
@media (max-width: 768px) {
  .leave-table-wrap { overflow-x:auto;-webkit-overflow-scrolling:touch; }
}
</style>
<div class="page" x-data="leavePage()" x-init="init()">

    {{-- ── Leave Request Modal ── --}}
    <template x-if="showModal">
        <div class="modal-overlay" @click.self="showModal = false">
            <div class="modal">
                <h3>{{ Auth::user()->isManager() ? 'Book Leave' : 'Request Leave' }}</h3>
                <form method="POST" action="{{ route('leave.store') }}">
                    @csrf

                    @if(Auth::user()->isManager())
                        <div class="form-group">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select" x-model="selectedEmployee" @change="recalc()" required>
                                <option value="">Select employee…</option>
                                @foreach($allEmployees as $emp)
                                    <option value="{{ $emp->id }}"
                                        data-days="{{ $emp->days_allowed }}"
                                        data-used="{{ $emp->leaveRequests->where('status','approved')->sum('days') }}">
                                        {{ $emp->name }} — {{ $emp->days_allowed - $emp->leaveRequests->where('status','approved')->sum('days') }} days left
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="employee_id" value="{{ Auth::id() }}">
                    @endif

                    <div class="form-group">
                        <label class="form-label">Leave type</label>
                        <select name="leave_type_id" class="form-select" required>
                            <option value="">— Select type —</option>
                            @foreach($leaveTypes as $lt)
                                <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:flex;gap:16px;margin-bottom:8px;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-check">
                                <input type="checkbox" name="is_half_day" value="1" x-model="isHalfDay" @change="onHalfDayChange()">
                                <strong>Half day</strong>
                            </label>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-check">
                                <input type="checkbox" name="is_short_leave" value="1" x-model="isShortLeave" @change="onShortLeaveChange()">
                                <strong>Short leave</strong> <span style="font-size:11px;color:#aaa;font-weight:400;">(hours only, no deduction)</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" name="start_date" class="form-input" x-model="startDate" @change="if(isHalfDay) endDate = startDate; if(isShortLeave) endDate = startDate; recalc()" required>
                        </div>
                        <div class="form-group" x-show="!isHalfDay && !isShortLeave">
                            <label class="form-label">End date</label>
                            <input type="date" name="end_date" class="form-input" x-model="endDate" @change="recalc()" required>
                        </div>
                    </div>

                    <template x-if="isHalfDay">
                        <div class="form-group">
                            <label class="form-label">Which half?</label>
                            <div style="display:flex;gap:16px;margin-top:4px;">
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                    <input type="radio" name="half_day_part" value="morning" x-model="halfDayPart" required>
                                    Morning
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                    <input type="radio" name="half_day_part" value="afternoon" x-model="halfDayPart">
                                    Afternoon
                                </label>
                            </div>
                        </div>
                    </template>

                    <template x-if="isShortLeave">
                        <div>
                            <div class="form-group">
                                <label class="form-label">Which half?</label>
                                <div style="display:flex;gap:16px;margin-top:4px;">
                                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                        <input type="radio" name="short_leave_part" value="morning" x-model="shortLeavePart">
                                        First half (AM)
                                    </label>
                                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;">
                                        <input type="radio" name="short_leave_part" value="afternoon" x-model="shortLeavePart">
                                        Second half (PM)
                                    </label>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">From</label>
                                    <input type="time" name="short_leave_from" class="form-input" x-model="shortLeaveFrom" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">To</label>
                                    <input type="time" name="short_leave_to" class="form-input" x-model="shortLeaveTo" required>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="workingDays > 0 && !isShortLeave">
                        <div class="days-info">
                            <strong x-text="workingDays"></strong> working day(s) &mdash; weekends &amp; public holidays excluded
                        </div>
                    </template>
                    <template x-if="isShortLeave && shortLeaveFrom && shortLeaveTo">
                        <div class="days-info">
                            Medical appt
                            <template x-if="shortLeavePart"><strong x-text="shortLeavePart === 'morning' ? '(AM)' : '(PM)'"></strong></template>
                            <strong x-text="shortLeaveFrom + ' – ' + shortLeaveTo"></strong> &mdash; no days deducted
                        </div>
                    </template>

                    <div class="form-group">
                        <label class="form-label">Notes (optional)</label>
                        <input type="text" name="reason" class="form-input" placeholder="e.g. Family holiday…" maxlength="500">
                    </div>

                    @if(Auth::user()->isAdmin())
                        <div class="form-group">
                            <label class="form-check">
                                <input type="checkbox" name="admin_override" value="1">
                                <strong>Admin override</strong> — bypass department concurrency limits
                            </label>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="error-msg" style="margin-bottom:12px;">{{ $errors->first() }}</div>
                    @endif

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="showModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            {{ Auth::user()->isManager() ? 'Book leave' : 'Request leave' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <div class="page-header">
        <h2>{{ Auth::user()->isManager() ? 'Manage Leave' : 'My Leave' }}</h2>
        <p>{{ Auth::user()->isManager() ? 'Review and manage all leave requests' : 'View and request your leave' }}</p>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0;">
        <div class="tab-row" style="margin-bottom:0;flex:1;">
            <div class="tab" :class="tab === 'pending' ? 'active' : ''" @click="tab = 'pending'">
                Pending <span style="font-size:11px;color:#aaa;" x-text="'(' + counts.pending + ')'"></span>
            </div>
            <div class="tab" :class="tab === 'approved' ? 'active' : ''" @click="tab = 'approved'">
                Approved <span style="font-size:11px;color:#aaa;" x-text="'(' + counts.approved + ')'"></span>
            </div>
            <div class="tab" :class="tab === 'rejected' ? 'active' : ''" @click="tab = 'rejected'">
                Rejected <span style="font-size:11px;color:#aaa;" x-text="'(' + counts.rejected + ')'"></span>
            </div>
            <div class="tab" :class="tab === 'all' ? 'active' : ''" @click="tab = 'all'">All</div>
            @if(Auth::user()->isManager())
            <div class="tab" :class="tab === 'allowances' ? 'active' : ''" @click="tab = 'allowances'">Allowances</div>
            @endif
        </div>
        <button class="btn btn-primary btn-sm" style="margin-left:12px;margin-bottom:20px;" @click="showModal = true">
            + {{ Auth::user()->isManager() ? 'Add Leave' : 'Request Leave' }}
        </button>
    </div>

    @if(Auth::user()->isManager())
    <div class="card" x-show="tab === 'allowances'">
        @if($allEmployees->isEmpty())
            <div class="empty-state">No employees found.</div>
        @else
        <div class="leave-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th style="text-align:center;">Allowed</th>
                    <th style="text-align:center;">Used</th>
                    <th style="text-align:center;">Remaining</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allEmployees as $emp)
                    @php
                        $used      = $emp->leaveRequests->sum('days');
                        $remaining = max(0, $emp->days_allowed - $used);
                        $pct       = $emp->days_allowed > 0 ? round(($used / $emp->days_allowed) * 100) : 0;
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                @if($emp->photoUrl())
                                    <img src="{{ $emp->photoUrl() }}" alt="{{ $emp->name }}"
                                         style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                                @else
                                    <div class="avatar" style="width:28px;height:28px;font-size:10px;flex-shrink:0;background:{{ $emp->color ?? '#38bdf8' }}33;color:{{ $emp->color ?? '#38bdf8' }}">
                                        {{ $emp->initials() }}
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight:500;font-size:13px;">{{ $emp->name }}</div>
                                    <div style="font-size:11px;color:#888;">{{ $emp->roleBadgeLabel() }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="text-align:center;font-size:13px;">{{ $emp->days_allowed }}</td>
                        <td style="text-align:center;font-size:13px;">{{ number_format($used, 1) }}</td>
                        <td style="text-align:center;">
                            <span style="font-size:13px;font-weight:600;color:{{ $remaining <= 5 ? '#ef4444' : ($remaining <= 10 ? '#f97316' : '#059669') }};">
                                {{ number_format($remaining, 1) }}
                            </span>
                            <div style="margin-top:4px;background:#f0f0f0;border-radius:999px;height:4px;width:80px;display:inline-block;vertical-align:middle;margin-left:6px;">
                                <div style="height:4px;border-radius:999px;width:{{ $pct }}%;background:{{ $pct >= 80 ? '#ef4444' : ($pct >= 60 ? '#f97316' : '#38bdf8') }};"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
    @endif

    <div class="card" x-show="tab !== 'allowances'">
        <template x-if="filtered.length === 0">
            <div class="empty-state">No <span x-text="tab === 'all' ? '' : tab"></span> leave requests.</div>
        </template>
        <template x-if="filtered.length > 0">
            <div class="leave-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="l in filtered" :key="l.id">
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="avatar" style="width:28px;height:28px;font-size:10px;"
                                         :style="'background:' + (l.employee.color || '#38bdf8') + '33;color:' + (l.employee.color || '#38bdf8')"
                                         x-text="initials(l.employee.name)">
                                    </div>
                                    <div>
                                        <div style="font-weight:500;font-size:13px;" x-text="l.employee.name"></div>
                                        <div style="font-size:11px;color:#888;" x-text="l.employee.role"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <template x-if="l.leave_type">
                                    <span style="font-size:12px;font-weight:500;padding:2px 8px;border-radius:999px;"
                                          :style="'background:' + l.leave_type.color + '33'"
                                          x-text="l.leave_type.name">
                                    </span>
                                </template>
                                <template x-if="!l.leave_type">
                                    <span style="font-size:12px;color:#bbb;">—</span>
                                </template>
                            </td>
                            <td style="font-size:12px;" x-text="l.is_short_leave ? fmt(l.start_date) : l.is_half_day ? fmt(l.start_date) + ' (' + l.half_day_part + ')' : fmt(l.start_date) + ' — ' + fmt(l.end_date)"></td>
                            <td>
                                <template x-if="l.is_short_leave">
                                    <div>
                                        <div style="font-size:11px;color:#7a4800;font-weight:500;" x-text="l.short_leave_part === 'morning' ? 'AM' : l.short_leave_part === 'afternoon' ? 'PM' : ''"></div>
                                        <div style="font-size:11px;color:#555;" x-text="l.short_leave_from + ' – ' + l.short_leave_to"></div>
                                    </div>
                                </template>
                                <template x-if="!l.is_short_leave">
                                    <strong x-text="l.days"></strong>
                                </template>
                            </td>
                            <td style="color:#555;font-size:12px;" x-text="l.reason || '—'"></td>
                            <td><span class="badge" :class="'badge-' + l.status" x-text="l.status"></span></td>
                            <td style="padding-left:16px;">
                                <div style="display:flex;gap:5px;align-items:center;">
                                    @if(Auth::user()->isManager())
                                        <template x-if="l.status === 'pending'">
                                            <span style="display:flex;gap:5px;">
                                                <form method="POST" :action="'/leave/' + l.id" style="display:inline;">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    @if(Auth::user()->isAdmin())
                                                        <input type="hidden" name="admin_override" value="0" x-bind:value="adminOverride ? '1' : '0'">
                                                    @endif
                                                    <button type="submit" class="icon-btn approve" title="Approve">
                                                        <svg width="14" height="14" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                                    </button>
                                                </form>
                                                <form method="POST" :action="'/leave/' + l.id" style="display:inline;">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="icon-btn reject" title="Reject">
                                                        <svg width="14" height="14" fill="none" stroke="#ef4444" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                    </button>
                                                </form>
                                            </span>
                                        </template>
                                    @endif
                                    <form method="POST" :action="'/leave/' + l.id" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="icon-btn danger" title="Delete" onclick="return confirm('Remove this leave request?')">
                                            <svg width="14" height="14" fill="none" stroke="#999" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            </div>
        </template>
    </div>

    @if($errors->has('department'))
        <div class="alert alert-error" style="margin-top:12px;">{{ $errors->first('department') }}</div>
    @endif
</div>

<script>
function leavePage() {
    const allLeaves   = @json($leavesData);
    const bankHolidays = @json($bankHolidays->pluck('date')->map(fn($d) => $d->toDateString()));

    return {
        tab: 'pending',
        showModal: {{ $errors->any() ? 'true' : 'false' }},
        startDate: '',
        endDate: '',
        isHalfDay: false,
        halfDayPart: 'morning',
        isShortLeave: false,
        shortLeaveFrom: '',
        shortLeaveTo: '',
        shortLeavePart: '',
        workingDays: 0,
        selectedEmployee: '',
        adminOverride: false,
        leaves: allLeaves,

        get counts() {
            return {
                pending:  this.leaves.filter(l => l.status === 'pending').length,
                approved: this.leaves.filter(l => l.status === 'approved').length,
                rejected: this.leaves.filter(l => l.status === 'rejected').length,
            };
        },

        get filtered() {
            if (this.tab === 'all') return this.leaves;
            return this.leaves.filter(l => l.status === this.tab);
        },

        init() {},

        initials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
        },

        fmt(d) {
            return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
        },

        onHalfDayChange() {
            if (this.isHalfDay) { this.isShortLeave = false; this.endDate = this.startDate; }
            this.recalc();
        },

        onShortLeaveChange() {
            if (this.isShortLeave) { this.isHalfDay = false; this.endDate = this.startDate; }
            else { this.shortLeavePart = ''; this.shortLeaveFrom = ''; this.shortLeaveTo = ''; }
            this.recalc();
        },

        recalc() {
            if (this.isHalfDay) {
                if (!this.startDate) { this.workingDays = 0; return; }
                const d = new Date(this.startDate);
                const dow = d.getDay();
                const ds = d.toISOString().slice(0, 10);
                this.workingDays = (dow !== 0 && dow !== 6 && !bankHolidays.includes(ds)) ? 0.5 : 0;
                return;
            }
            if (!this.startDate || !this.endDate) { this.workingDays = 0; return; }
            let count = 0;
            const d = new Date(this.startDate);
            const e = new Date(this.endDate);
            while (d <= e) {
                const dow = d.getDay();
                const ds = d.toISOString().slice(0, 10);
                if (dow !== 0 && dow !== 6 && !bankHolidays.includes(ds)) count++;
                d.setDate(d.getDate() + 1);
            }
            this.workingDays = count;
        },
    };
}
</script>
</x-app-layout>
