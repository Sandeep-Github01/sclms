@include('frontend.partials.header')

<div class="login-page">
    <div class="auth-container">
        <h2>Forgot Your Password?</h2>

        @if(session('status'))
            <p class="success">{{ session('status') }}</p>
        @endif

        @if($errors->any())
            <div class="alert">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('frontend.user.forgot-password.send') }}">
            @csrf
            <label>Email</label>
            <input type="email" name="email" required>
            <button type="submit">Send Reset Link</button>
        </form>

        <p style="text-align:center;">
            <a href="{{ route('frontend.user.login') }}">Back to Login</a>
        </p>
    </div>
</div>

@include('frontend.partials.footer')
