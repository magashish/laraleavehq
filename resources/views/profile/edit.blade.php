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
