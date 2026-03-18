<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LeaveHQ — Sign in</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <h1>LeaveHQ</h1>
        <p>Sign in to manage your holidays</p>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="error-msg">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email address</label>
                <input id="email" class="form-input" type="email" name="email" value="{{ old('email') }}"
                    placeholder="you@company.com" required autofocus autocomplete="username" />
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input id="password" class="form-input" type="password" name="password"
                    placeholder="••••••••" required autocomplete="current-password" />
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px;">
                Sign in
            </button>
        </form>

        <p style="margin-top:20px;font-size:12px;color:#aaa;text-align:center;">
            Default: admin@company.com / admin123
        </p>
    </div>
</div>
</body>
</html>
