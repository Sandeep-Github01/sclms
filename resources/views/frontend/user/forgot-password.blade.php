@include('frontend.partials.header')

<div class="login-page">
    <div class="auth-container">
        <h2>Forgot Password</h2>

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

        <form method="POST" action="{{ route('frontend.user.forgot-password.send') }}">
            @csrf

            <label>Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>

            <button type="submit">Send Password Reset Link</button>
        </form>

        <div class="register-container">
            <p>
                <a href="{{ route('frontend.user.login') }}">Back to Login</a>
            </p>
        </div>
    </div>
</div>

@include('frontend.partials.footer')