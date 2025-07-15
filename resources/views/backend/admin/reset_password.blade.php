@include('backend.partials.header')

<div class="login-page">
    <div class="auth-container">
        <h2>Admin - Reset Password</h2>

        @if (session('status'))
            <p class="success">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div class="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.reset-password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <label>Email Address</label>
            <input type="email" name="email" value="{{ $email ?? old('email') }}" required autofocus>

            <label>New Password</label>
            <input type="password" name="password" required>

            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required>

            <button type="submit">Reset Password</button>
        </form>

        <p style="text-align:center;">
            <a href="{{ route('admin.login') }}">Back to Login</a>
        </p>
    </div>
</div>

@include('backend.partials.footer')
