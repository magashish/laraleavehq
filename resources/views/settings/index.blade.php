<x-app-layout>
<div class="page" x-data="{
    tab: 'employees',
    showEmpModal: false,
    showEditEmpModal: false,
    showBHModal: false,
    showLTModal: false,
    showEditLTModal: false,
    showDeptModal: false,
    showEditDeptModal: false,
    empColor: '#38bdf8',
    editEmpColor: '#38bdf8',
    editEmp: {},
    editLT: {},
    editDept: {},
}">

    {{-- ── Add Employee Modal ── --}}
    <template x-if="showEmpModal">
        <div class="modal-overlay" @click.self="showEmpModal = false">
            <div class="modal">
                <h3>Add Employee</h3>
                <form method="POST" action="{{ route('settings.employees.add') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full name</label>
                            <input type="text" name="name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Job title</label>
                            <input type="text" name="role" class="form-input" placeholder="e.g. Designer" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Access level</label>
                            <select name="role_type" class="form-select" required>
                                <option value="employee">Employee</option>
                                <option value="manager">Manager</option>
                                <option value="contractor">Contractor (no holiday allowance)</option>
                                <option value="intern">Intern (no holiday allowance)</option>
                                @if(Auth::user()->isAdmin())
                                    <option value="admin">Admin (full access)</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Annual leave days</label>
                            <input type="number" name="days_allowed" class="form-input" value="25" min="0" max="60" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Colour</label>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:4px;">
                            @foreach(['#e879f9','#38bdf8','#fb923c','#a78bfa','#34d399','#f472b6','#facc15','#60a5fa'] as $c)
                                <label style="cursor:pointer;">
                                    <input type="radio" name="_color_pick" value="{{ $c }}" style="display:none;" x-on:change="empColor = '{{ $c }}'">
                                    <div style="width:28px;height:28px;border-radius:50%;background:{{ $c }};cursor:pointer;"
                                         :style="{ outline: empColor === '{{ $c }}' ? '3px solid #1c2b3a' : '', 'outline-offset': empColor === '{{ $c }}' ? '2px' : '' }"
                                         @click="empColor = '{{ $c }}'"></div>
                                </label>
                            @endforeach
                            <input type="hidden" name="color" :value="empColor">
                        </div>
                    </div>
                    <div style="background:#f5f4f0;border-radius:7px;padding:8px 12px;font-size:12px;color:#666;margin-bottom:16px;">
                        Default password: <strong>password123</strong> — ask them to change it on first login.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="showEmpModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add employee</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- ── Edit Employee Modal ── --}}
    <template x-if="showEditEmpModal">
        <div class="modal-overlay" @click.self="showEditEmpModal = false">
            <div class="modal">
                <h3>Edit Employee</h3>
                <form method="POST" :action="'/settings/employees/' + editEmp.id">
                    @csrf @method('PATCH')
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full name</label>
                            <input type="text" name="name" class="form-input" :value="editEmp.name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" :value="editEmp.email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Job title</label>
                            <input type="text" name="role" class="form-input" :value="editEmp.role" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Access level</label>
                            <select name="role_type" class="form-select" required>
                                <option value="employee" :selected="editEmp.role_type === 'employee'">Employee</option>
                                <option value="manager" :selected="editEmp.role_type === 'manager'">Manager</option>
                                <option value="contractor" :selected="editEmp.role_type === 'contractor'">Contractor (no holiday allowance)</option>
                                <option value="intern" :selected="editEmp.role_type === 'intern'">Intern (no holiday allowance)</option>
                                @if(Auth::user()->isAdmin())
                                    <option value="admin" :selected="editEmp.role_type === 'admin'">Admin (full access)</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Annual leave days</label>
                            <input type="number" name="days_allowed" class="form-input" :value="editEmp.days_allowed" min="0" max="60" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Colour</label>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:4px;">
                            @foreach(['#e879f9','#38bdf8','#fb923c','#a78bfa','#34d399','#f472b6','#facc15','#60a5fa'] as $c)
                                <label style="cursor:pointer;">
                                    <input type="radio" name="_edit_color_pick" value="{{ $c }}" style="display:none;" x-on:change="editEmpColor = '{{ $c }}'">
                                    <div style="width:28px;height:28px;border-radius:50%;background:{{ $c }};cursor:pointer;"
                                         :style="{ outline: editEmpColor === '{{ $c }}' ? '3px solid #1c2b3a' : '', 'outline-offset': editEmpColor === '{{ $c }}' ? '2px' : '' }"
                                         @click="editEmpColor = '{{ $c }}'"></div>
                                </label>
                            @endforeach
                            <input type="hidden" name="color" :value="editEmpColor">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="showEditEmpModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- ── Add Bank Holiday Modal ── --}}
    <template x-if="showBHModal">
        <div class="modal-overlay" @click.self="showBHModal = false">
            <div class="modal">
                <h3>Add Bank Holiday</h3>
                <form method="POST" action="{{ route('settings.bank-holidays.add') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-input" placeholder="e.g. Christmas Day" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="showBHModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add bank holiday</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- ── Add Leave Type Modal ── --}}
    <template x-if="showLTModal">
        <div class="modal-overlay" @click.self="showLTModal = false">
            <div class="modal">
                <h3>Add Leave Type</h3>
                <form method="POST" action="{{ route('settings.leave-types.add') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-input" placeholder="e.g. Compassionate Leave" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Colour (hex)</label>
                            <input type="color" name="color" class="form-input" value="#38bdf8" style="height:42px;padding:4px;" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="counts_toward_allowance" value="1" checked>
                            Counts toward annual leave allowance
                        </label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="showLTModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add leave type</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- ── Edit Leave Type Modal ── --}}
    <template x-if="showEditLTModal">
        <div class="modal-overlay" @click.self="showEditLTModal = false">
            <div class="modal">
                <h3>Edit Leave Type</h3>
                <form method="POST" :action="'/settings/leave-types/' + editLT.id">
                    @csrf @method('PATCH')
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-input" :value="editLT.name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Colour</label>
                            <input type="color" name="color" class="form-input" :value="editLT.color" style="height:42px;padding:4px;" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="counts_toward_allowance" value="1" :checked="editLT.counts_toward_allowance">
                            Counts toward annual leave allowance
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="is_active" value="1" :checked="editLT.is_active">
                            Active (visible when booking leave)
                        </label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="showEditLTModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- ── Add Department Modal ── --}}
    <template x-if="showDeptModal">
        <div class="modal-overlay" @click.self="showDeptModal = false">
            <div class="modal">
                <h3>Add Department</h3>
                <form method="POST" action="{{ route('settings.departments.add') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Department name</label>
                            <input type="text" name="name" class="form-input" placeholder="e.g. Operations" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max concurrent absences</label>
                            <input type="number" name="max_concurrent" class="form-input" value="1" min="1" max="20" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="showDeptModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add department</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- ── Edit Department Modal ── --}}
    <template x-if="showEditDeptModal">
        <div class="modal-overlay" @click.self="showEditDeptModal = false">
            <div class="modal">
                <h3>Edit Department: <span x-text="editDept.name"></span></h3>
                <form method="POST" :action="'/settings/departments/' + editDept.id + '/members'">
                    @csrf @method('PATCH')
                    <div class="form-group">
                        <label class="form-label" style="margin-bottom:10px;">Members (select all that apply)</label>
                        <div style="display:flex;flex-direction:column;gap:6px;max-height:250px;overflow-y:auto;">
                            @foreach($employees as $emp)
                                <label class="form-check">
                                    <input type="checkbox" name="user_ids[]" value="{{ $emp->id }}"
                                        :checked="editDept.member_ids && editDept.member_ids.includes({{ $emp->id }})">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div class="avatar" style="width:22px;height:22px;font-size:9px;background:{{ $emp->color }}33;color:{{ $emp->color }}">{{ $emp->initials() }}</div>
                                        {{ $emp->name }} <span style="font-size:11px;color:#aaa;">({{ $emp->role }})</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-row" style="margin-top:12px;">
                        <div class="form-group">
                            <label class="form-label">Department name</label>
                            <input type="text" name="_name" style="display:none">
                            {{-- name/max updated via separate form --}}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="showEditDeptModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save members</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <div class="page-header">
        <h2>Settings</h2>
        <p>Manage employees, leave types, departments, and bank holidays</p>
    </div>

    <div class="tab-row">
        <div class="tab" :class="tab === 'employees' ? 'active' : ''" @click="tab = 'employees'">
            Employees <span style="font-size:11px;color:#aaa;">({{ $employees->count() }})</span>
        </div>
        <div class="tab" :class="tab === 'leavetypes' ? 'active' : ''" @click="tab = 'leavetypes'">
            Leave Types <span style="font-size:11px;color:#aaa;">({{ $leaveTypes->count() }})</span>
        </div>
        <div class="tab" :class="tab === 'departments' ? 'active' : ''" @click="tab = 'departments'">
            Departments <span style="font-size:11px;color:#aaa;">({{ $departments->count() }})</span>
        </div>
        <div class="tab" :class="tab === 'bankholidays' ? 'active' : ''" @click="tab = 'bankholidays'">
            Bank Holidays
        </div>
    </div>

    {{-- ── Employees Tab ── --}}
    <div x-show="tab === 'employees'">
        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:12px;">{{ $errors->first() }}</div>
        @endif
        <div class="card">
            <div class="card-title">
                Employees ({{ $employees->count() }})
                <button class="btn btn-primary btn-sm" @click="showEmpModal = true">+ Add employee</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Job title</th>
                        <th>Access</th>
                        <th>Annual days</th>
                        <th>Used</th>
                        <th>Remaining</th>
                        <th>Departments</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                        @php $used = $emp->used_days ?? 0; $remaining = $emp->days_allowed - $used; @endphp
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    @if($emp->profile_photo)
                                        <img src="{{ $emp->photoUrl() }}" alt="{{ $emp->name }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
                                    @else
                                        <div class="avatar" style="width:28px;height:28px;font-size:10px;background:{{ $emp->color }}33;color:{{ $emp->color }}">
                                            {{ $emp->initials() }}
                                        </div>
                                    @endif
                                    <div>
                                        <div style="font-weight:500;font-size:13px;">{{ $emp->name }}</div>
                                        <div style="font-size:11px;color:#888;">{{ $emp->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="color:#555;font-size:13px;">{{ $emp->role }}</td>
                            <td>
                                @php
                                    $badgeColors = [
                                        'admin'      => '#ef4444',
                                        'manager'    => '#3b82f6',
                                        'employee'   => '#22c55e',
                                        'contractor' => '#f97316',
                                        'intern'     => '#a78bfa',
                                    ];
                                    $bc = $badgeColors[$emp->role_type] ?? '#888';
                                @endphp
                                <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:999px;background:{{ $bc }}22;color:{{ $bc }};">
                                    {{ $emp->roleBadgeLabel() }}
                                </span>
                            </td>
                            <td style="font-size:13px;">{{ $emp->days_allowed }}</td>
                            <td style="font-size:13px;">{{ $used }}</td>
                            <td style="font-size:13px;font-weight:600;color:{{ $remaining < 5 ? '#ef4444' : '#059669' }}">
                                {{ $remaining }}
                            </td>
                            <td style="font-size:12px;color:#666;">
                                {{ $emp->departments->pluck('name')->join(', ') ?: '—' }}
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;">
                                    <button class="btn btn-outline btn-sm"
                                        @click="editEmp = {
                                            id: {{ $emp->id }},
                                            name: '{{ addslashes($emp->name) }}',
                                            email: '{{ $emp->email }}',
                                            role: '{{ addslashes($emp->role) }}',
                                            role_type: '{{ $emp->role_type }}',
                                            days_allowed: {{ $emp->days_allowed }},
                                            color: '{{ $emp->color }}'
                                        }; editEmpColor = '{{ $emp->color }}'; showEditEmpModal = true">
                                        Edit
                                    </button>
                                    @if($emp->id !== Auth::id())
                                        <form method="POST" action="{{ route('settings.employees.remove', $emp) }}"
                                            onsubmit="return confirm('Remove {{ $emp->name }}? Their leave history will also be deleted.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline btn-sm" style="color:#999;">Remove</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Leave Types Tab ── --}}
    <div x-show="tab === 'leavetypes'">
        <div class="card">
            <div class="card-title">
                Leave Types ({{ $leaveTypes->count() }})
                <button class="btn btn-primary btn-sm" @click="showLTModal = true">+ Add leave type</button>
            </div>
            @if($leaveTypes->isEmpty())
                <div class="empty-state">No leave types defined yet.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Colour</th>
                            <th>Counts toward allowance</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaveTypes as $lt)
                            <tr>
                                <td style="font-weight:500;font-size:13px;">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div style="width:12px;height:12px;border-radius:50%;background:{{ $lt->color }};flex-shrink:0;"></div>
                                        {{ $lt->name }}
                                    </div>
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <div style="width:20px;height:20px;border-radius:4px;background:{{ $lt->color }};"></div>
                                        <span style="font-size:12px;color:#888;">{{ $lt->color }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($lt->counts_toward_allowance)
                                        <span style="color:#059669;font-size:12px;font-weight:500;">Yes</span>
                                    @else
                                        <span style="color:#aaa;font-size:12px;">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lt->is_active)
                                        <span style="color:#059669;font-size:12px;">Active</span>
                                    @else
                                        <span style="color:#aaa;font-size:12px;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <button class="btn btn-outline btn-sm"
                                            @click="editLT = {
                                                id: {{ $lt->id }},
                                                name: '{{ addslashes($lt->name) }}',
                                                color: '{{ $lt->color }}',
                                                counts_toward_allowance: {{ $lt->counts_toward_allowance ? 'true' : 'false' }},
                                                is_active: {{ $lt->is_active ? 'true' : 'false' }}
                                            }; showEditLTModal = true">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('settings.leave-types.remove', $lt) }}"
                                            onsubmit="return confirm('Remove leave type {{ $lt->name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline btn-sm" style="color:#999;">✕</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── Departments Tab ── --}}
    <div x-show="tab === 'departments'">
        <div class="card">
            <div class="card-title">
                Departments ({{ $departments->count() }})
                <button class="btn btn-primary btn-sm" @click="showDeptModal = true">+ Add department</button>
            </div>
            @if($departments->isEmpty())
                <div class="empty-state">No departments defined. Add departments to enable leave concurrency rules.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Max concurrent absences</th>
                            <th>Members</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $dept)
                            <tr>
                                <td style="font-weight:500;font-size:13px;">{{ $dept->name }}</td>
                                <td>
                                    <form method="POST" action="{{ route('settings.departments.update', $dept) }}" style="display:flex;align-items:center;gap:6px;">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="name" value="{{ $dept->name }}">
                                        <input type="number" name="max_concurrent" value="{{ $dept->max_concurrent }}"
                                            min="1" max="20"
                                            style="width:60px;padding:4px 8px;border:1px solid #d5d2cc;border-radius:6px;font-size:13px;">
                                        <button type="submit" class="btn btn-success btn-sm">Save</button>
                                    </form>
                                </td>
                                <td>
                                    <div style="display:flex;gap:4px;flex-wrap:wrap;align-items:center;">
                                        @forelse($dept->users as $member)
                                            @if($member->profile_photo)
                                                <img src="{{ $member->photoUrl() }}" title="{{ $member->name }}"
                                                    style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
                                            @else
                                                <div class="avatar" title="{{ $member->name }}"
                                                    style="width:24px;height:24px;font-size:9px;background:{{ $member->color }}33;color:{{ $member->color }}">
                                                    {{ $member->initials() }}
                                                </div>
                                            @endif
                                        @empty
                                            <span style="font-size:12px;color:#aaa;">No members</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <button class="btn btn-outline btn-sm"
                                            @click="editDept = {
                                                id: {{ $dept->id }},
                                                name: '{{ addslashes($dept->name) }}',
                                                member_ids: [{{ $dept->users->pluck('id')->join(',') }}]
                                            }; showEditDeptModal = true">
                                            Edit members
                                        </button>
                                        <form method="POST" action="{{ route('settings.departments.remove', $dept) }}"
                                            onsubmit="return confirm('Remove {{ $dept->name }} department?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline btn-sm" style="color:#999;">✕</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div style="padding:12px 0;font-size:12px;color:#888;">
            <strong>Note:</strong> An Admin can override department concurrency limits when booking or approving leave (e.g. for Christmas).
            Staff can appear in multiple departments.
        </div>
    </div>

    {{-- ── Bank Holidays Tab ── --}}
    <div x-show="tab === 'bankholidays'">
        @php
            $today = now()->toDateString();
            $upcoming = $bankHolidays->filter(fn($b) => $b->date->toDateString() >= $today);
            $past = $bankHolidays->filter(fn($b) => $b->date->toDateString() < $today);
        @endphp
        <div class="card">
            <div class="card-title">
                Upcoming Bank Holidays ({{ $upcoming->count() }})
                <button class="btn btn-primary btn-sm" @click="showBHModal = true">+ Add</button>
            </div>

            @if($upcoming->isEmpty())
                <div class="empty-state">No upcoming bank holidays.</div>
            @else
                @foreach($upcoming as $bh)
                    <div class="bh-item">
                        <div>
                            <div style="font-weight:500;font-size:13px;">{{ $bh->name }}</div>
                            <div class="bh-date">{{ $bh->date->format('l, j F Y') }}</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="badge badge-bank">Bank holiday</span>
                            <form method="POST" action="{{ route('settings.bank-holidays.remove', $bh) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline btn-sm" style="color:#999;">✕</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif

            @if($past->isNotEmpty())
                <div style="font-size:11px;color:#aaa;margin:16px 0 8px;text-transform:uppercase;letter-spacing:0.5px;">Past</div>
                @foreach($past as $bh)
                    <div class="bh-item" style="opacity:0.5;">
                        <div>
                            <div style="font-weight:500;font-size:13px;">{{ $bh->name }}</div>
                            <div class="bh-date">{{ $bh->date->format('j F Y') }}</div>
                        </div>
                        <form method="POST" action="{{ route('settings.bank-holidays.remove', $bh) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-sm" style="color:#999;">✕</button>
                        </form>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

</div>
</x-app-layout>
