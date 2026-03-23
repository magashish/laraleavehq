<section>
    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="form-group">
            <label class="form-label">Current Password</label>
            <input id="update_password_current_password" name="current_password" type="password"
                   class="form-input" autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="error-msg">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">New Password</label>
            <input id="update_password_password" name="password" type="password"
                   class="form-input" autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="error-msg">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                   class="form-input" autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <div class="error-msg">{{ $message }}</div>
            @enderror
        </div>

        <div style="display:flex;align-items:center;gap:12px;margin-top:8px;">
            <button type="submit" class="btn btn-primary">Save</button>
            @if (session('status') === 'password-updated')
                <span style="font-size:13px;color:#22c55e;">Saved.</span>
            @endif
        </div>
    </form>
</section>
