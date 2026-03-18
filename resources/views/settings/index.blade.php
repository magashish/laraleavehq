<x-app-layout>
<div class="page" x-data="{ tab: 'employees', showEmpModal: false, showBHModal: false, empColor: '#38bdf8' }">

    {{-- Add Employee Modal --}}
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
                            <label class="form-label">Job title / role</label>
                            <input type="text" name="role" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Annual leave days</label>
                            <input type="number" name="days_allowed" class="form-input" value="25" min="1" max="60" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Colour</label>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            @foreach(['#e879f9','#38bdf8','#fb923c','#a78bfa','#34d399','#f472b6','#facc15','#60a5fa'] as $c)
                                <label style="cursor:pointer;">
                                    <input type="radio" name="color" value="{{ $c }}" style="display:none;" x-on:change="empColor = '{{ $c }}'">
                                    <div style="width:28px;height:28px;border-radius:50%;background:{{ $c }};cursor:pointer;"
                                         :style="empColor === '{{ $c }}' ? 'border:3px solid #1c2b3a;' : 'border:2px solid transparent;'"
                                         @click="empColor = '{{ $c }}'"></div>
                                </label>
                            @endforeach
                            <input type="hidden" name="color" :value="empColor">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="is_manager" value="1">
                            Manager (can approve leave &amp; access all pages)
                        </label>
                    </div>
                    <div style="background:#f5f4f0;border-radius:7px;padding:8px 12px;font-size:12px;color:#666;margin-bottom:16px;">
                        Default password will be <strong>password123</strong> — ask them to change it on first login.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="showEmpModal = false">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add employee</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Add Bank Holiday Modal --}}
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

    <div class="page-header">
        <h2>Settings</h2>
        <p>Manage employees and bank holidays</p>
    </div>

    <div class="tab-row">
        <div class="tab" :class="tab === 'employees' ? 'active' : ''" @click="tab = 'employees'">Employees</div>
        <div class="tab" :class="tab === 'bankholidays' ? 'active' : ''" @click="tab = 'bankholidays'">Bank Holidays</div>
    </div>

    {{-- Employees Tab --}}
    <div x-show="tab === 'employees'">
        <div class="card">
            <div class="card-title">
                Employees ({{ $employees->count() }})
                <button class="btn btn-primary btn-sm" @click="showEmpModal = true">+ Add employee</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Annual days</th>
                        <th>Used</th>
                        <th>Remaining</th>
                        <th>Access</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                        @php $used = $emp->used_days ?? 0; $remaining = $emp->days_allowed - $used; @endphp
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="avatar" style="width:28px;height:28px;font-size:10px;background:{{ $emp->color }}33;color:{{ $emp->color }}">
                                        {{ $emp->initials() }}
                                    </div>
                                    <div>
                                        <div style="font-weight:500;font-size:13px;">{{ $emp->name }}</div>
                                        <div style="font-size:11px;color:#888;">{{ $emp->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="color:#555;font-size:13px;">{{ $emp->role }}</td>
                            <td>
                                <form method="POST" action="{{ route('settings.employees.days', $emp) }}" style="display:flex;align-items:center;gap:6px;">
                                    @csrf @method('PATCH')
                                    <input type="number" name="days_allowed" value="{{ $emp->days_allowed }}"
                                        min="1" max="60"
                                        style="width:60px;padding:4px 8px;border:1px solid #d5d2cc;border-radius:6px;font-size:13px;">
                                    <button type="submit" class="btn btn-success btn-sm">Save</button>
                                </form>
                            </td>
                            <td style="font-size:13px;">{{ $used }}</td>
                            <td style="font-size:13px;font-weight:600;color:{{ $remaining < 5 ? '#ef4444' : '#059669' }}">
                                {{ $remaining }}
                            </td>
                            <td>
                                @if($emp->is_manager)
                                    <span class="badge badge-manager">Manager</span>
                                @else
                                    <span style="font-size:12px;color:#aaa;">Employee</span>
                                @endif
                            </td>
                            <td>
                                @if($emp->id !== Auth::id())
                                    <form method="POST" action="{{ route('settings.employees.remove', $emp) }}"
                                        onsubmit="return confirm('Remove {{ $emp->name }}? Their leave history will also be deleted.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline btn-sm" style="color:#999;">Remove</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bank Holidays Tab --}}
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
                <div class="empty-state">No upcoming bank holidays. Add some above.</div>
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
