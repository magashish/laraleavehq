<section>
    <p style="font-size:13px;color:#666;margin-bottom:16px;">
        Once your account is deleted, all of its resources and data will be permanently deleted.
        Before deleting your account, please download any data or information that you wish to retain.
    </p>

    <button type="button" class="btn btn-outline" style="color:#ef4444;border-color:#ef4444;"
        onclick="document.getElementById('confirm-delete-modal').style.display='flex'">
        Delete Account
    </button>

    {{-- Confirmation Modal --}}
    <div id="confirm-delete-modal"
         style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
        <div class="card" style="width:100%;max-width:480px;margin:0 20px;">
            <div class="card-title">Are you sure you want to delete your account?</div>
            <p style="font-size:13px;color:#666;margin-bottom:16px;">
                Once your account is deleted, all of its resources and data will be permanently deleted.
                Please enter your password to confirm.
            </p>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div class="form-group">
                    <label class="form-label sr-only">Password</label>
                    <input id="password" name="password" type="password"
                           class="form-input" placeholder="Password">
                    @error('password', 'userDeletion')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="modal-footer" style="padding:0;margin-top:16px;">
                    <button type="button" class="btn btn-outline"
                        onclick="document.getElementById('confirm-delete-modal').style.display='none'">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" style="background:#ef4444;border-color:#ef4444;">
                        Delete Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
