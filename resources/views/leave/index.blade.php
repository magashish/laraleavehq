<x-app-layout>
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
                        <select name="leave_type_id" class="form-select">
                            <option value="">— Select type —</option>
                            @foreach($leaveTypes as $lt)
                                <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Start date</label>
                            <input type="date" name="start_date" class="form-input" x-model="startDate" @change="recalc()" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">End date</label>
                            <input type="date" name="end_date" class="form-input" x-model="endDate" @change="recalc()" required>
                        </div>
                    </div>

                    <template x-if="workingDays > 0">
                        <div class="days-info">
                            <strong x-text="workingDays"></strong> working day(s) &mdash; weekends &amp; bank holidays excluded
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
        </div>
        <button class="btn btn-primary btn-sm" style="margin-left:12px;margin-bottom:20px;" @click="showModal = true">
            + {{ Auth::user()->isManager() ? 'Add Leave' : 'Request Leave' }}
        </button>
    </div>

    <div class="card">
        <template x-if="filtered.length === 0">
            <div class="empty-state">No <span x-text="tab === 'all' ? '' : tab"></span> leave requests.</div>
        </template>
        <template x-if="filtered.length > 0">
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
                                          :style="'background:' + l.leave_type.color + '22;color:' + l.leave_type.color"
                                          x-text="l.leave_type.name">
                                    </span>
                                </template>
                                <template x-if="!l.leave_type">
                                    <span style="font-size:12px;color:#bbb;">—</span>
                                </template>
                            </td>
                            <td style="font-size:12px;" x-text="fmt(l.start_date) + ' — ' + fmt(l.end_date)"></td>
                            <td><strong x-text="l.days"></strong></td>
                            <td style="color:#555;font-size:12px;" x-text="l.reason || '—'"></td>
                            <td><span class="badge" :class="'badge-' + l.status" x-text="l.status"></span></td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    @if(Auth::user()->isManager())
                                        <template x-if="l.status === 'pending'">
                                            <span style="display:flex;gap:4px;">
                                                <form method="POST" :action="'/leave/' + l.id" style="display:inline;">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    @if(Auth::user()->isAdmin())
                                                        <input type="hidden" name="admin_override" value="0" x-bind:value="adminOverride ? '1' : '0'">
                                                    @endif
                                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                </form>
                                                <form method="POST" :action="'/leave/' + l.id" style="display:inline;">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                                </form>
                                            </span>
                                        </template>
                                    @endif
                                    <form method="POST" :action="'/leave/' + l.id" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline btn-sm"
                                            onclick="return confirm('Remove this leave request?')"
                                            style="color:#999;">✕</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
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

        recalc() {
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
