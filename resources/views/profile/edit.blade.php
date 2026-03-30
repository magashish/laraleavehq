<x-app-layout>
<div class="page">
    <div class="page-header">
        <h2>Profile</h2>
        <p>Manage your account details and photo</p>
    </div>

    @if(session('status') === 'profile-updated')
        <div class="alert alert-success" style="margin-bottom:16px;">Profile updated.</div>
    @endif
    @if(session('status') === 'photo-updated')
        <div class="alert alert-success" style="margin-bottom:16px;">Photo updated.</div>
    @endif
    @if(session('status') === 'photo-removed')
        <div class="alert alert-success" style="margin-bottom:16px;">Photo removed.</div>
    @endif

    {{-- ── Photo ── --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-title">Profile Photo</div>
        <div style="display:flex;align-items:center;gap:20px;">
            @if($user->profile_photo)
                <img src="{{ $user->photoUrl() }}" alt="{{ $user->name }}"
                    style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid #e5e2dc;">
            @else
                <div class="avatar" style="width:72px;height:72px;font-size:22px;background:{{ $user->color }}33;color:{{ $user->color }}">
                    {{ $user->initials() }}
                </div>
            @endif
            <div style="flex:1;">
                <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    @csrf
                    <input type="file" name="profile_photo" accept="image/*" class="form-input" style="flex:1;min-width:200px;" required>
                    <button type="submit" class="btn btn-primary btn-sm">Upload photo</button>
                </form>
                @error('profile_photo')
                    <div class="error-msg" style="margin-top:6px;">{{ $message }}</div>
                @enderror
                @if($user->profile_photo)
                    <form method="POST" action="{{ route('profile.photo.remove') }}" style="margin-top:8px;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline btn-sm" style="color:#999;"
                            onclick="return confirm('Remove your photo?')">Remove photo</button>
                    </form>
                @endif
                <p style="font-size:11px;color:#aaa;margin-top:6px;">Max 2MB. JPEG, PNG, GIF, or WebP.</p>
            </div>
        </div>
    </div>

    {{-- ── Profile info ── --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-title">Account Details</div>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PATCH')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="error-msg">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $user->email) }}" required>
                    @error('email') <div class="error-msg">{{ $message }}</div> @enderror
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px;">
                <div>
                    <div style="font-size:12px;color:#888;margin-bottom:2px;">Access level</div>
                    <div style="font-size:13px;font-weight:500;">{{ $user->roleBadgeLabel() }}</div>
                </div>
                <div>
                    <div style="font-size:12px;color:#888;margin-bottom:2px;">Job title</div>
                    <div style="font-size:13px;">{{ $user->role }}</div>
                </div>
                <div>
                    <div style="font-size:12px;color:#888;margin-bottom:2px;">Annual allowance</div>
                    <div style="font-size:13px;">
                        @if($user->hasHolidayAllowance())
                            {{ $user->remainingDays() }} remaining of {{ $user->days_allowed }}
                        @else
                            N/A ({{ $user->roleBadgeLabel() }})
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding:0;">
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </div>

    {{-- ── Calendar sync ── --}}
    <div class="card" style="margin-bottom:20px;" x-data="{ copied: false }">
        <div class="card-title">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Calendar Sync
            </div>
        </div>
        <p style="font-size:13px;color:#555;margin-bottom:16px;">
            Subscribe to your approved leave in Google Calendar, Outlook, or Apple Calendar.
            The calendar updates automatically when leave is approved.
        </p>

        @php $calUrl = route('calendar.feed', ['token' => Auth::user()->calendarToken()]); @endphp

        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <input type="text" value="{{ $calUrl }}" readonly
                style="flex:1;min-width:200px;padding:8px 12px;border:1px solid #d5d2cc;border-radius:6px;font-size:12px;color:#555;background:#f9fafb;font-family:monospace;">
            <button class="btn btn-outline btn-sm"
                x-on:click="navigator.clipboard.writeText('{{ $calUrl }}'); copied = true; setTimeout(() => copied = false, 2000)">
                <span x-show="!copied">Copy URL</span>
                <span x-show="copied" style="color:#059669;">Copied!</span>
            </button>
        </div>

        <div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap;">
            <a href="https://calendar.google.com/calendar/r?cid={{ urlencode($calUrl) }}"
                target="_blank" rel="noopener"
                style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:500;color:#2563eb;text-decoration:none;padding:8px 14px;border:1px solid #d5d2cc;border-radius:6px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Add to Google Calendar
            </a>
        </div>

        @if(Auth::user()->isManager())
            @php $teamCalUrl = route('calendar.team-feed', ['token' => Auth::user()->calendarToken()]); @endphp
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid #e5e7eb;" x-data="{ copiedTeam: false }">
                <div style="font-size:13px;font-weight:600;color:#111827;margin-bottom:6px;">Team / Organisation feed</div>
                <p style="font-size:12px;color:#666;margin-bottom:10px;">Shows approved leave for all employees. Only accessible with a manager or admin token.</p>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <input type="text" value="{{ $teamCalUrl }}" readonly
                        style="flex:1;min-width:200px;padding:8px 12px;border:1px solid #d5d2cc;border-radius:6px;font-size:12px;color:#555;background:#f9fafb;font-family:monospace;">
                    <button class="btn btn-outline btn-sm"
                        x-on:click="navigator.clipboard.writeText('{{ $teamCalUrl }}'); copiedTeam = true; setTimeout(() => copiedTeam = false, 2000)">
                        <span x-show="!copiedTeam">Copy URL</span>
                        <span x-show="copiedTeam" style="color:#059669;">Copied!</span>
                    </button>
                </div>
                <div style="margin-top:10px;">
                    <a href="https://calendar.google.com/calendar/r?cid={{ urlencode($teamCalUrl) }}"
                        target="_blank" rel="noopener"
                        style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:500;color:#2563eb;text-decoration:none;padding:8px 14px;border:1px solid #d5d2cc;border-radius:6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        Add to Google Calendar
                    </a>
                </div>
            </div>
        @endif

        <p style="font-size:11px;color:#aaa;margin-top:12px;">
            Keep these URLs private — anyone with them can view the leave calendar.
        </p>
    </div>

    {{-- ── Change password ── --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-title">Change Password</div>
        @include('profile.partials.update-password-form')
    </div>

    {{-- ── Delete account ── --}}
    <div class="card">
        <div class="card-title" style="color:#ef4444;">Danger Zone</div>
        @include('profile.partials.delete-user-form')
    </div>
</div>
</x-app-layout>
