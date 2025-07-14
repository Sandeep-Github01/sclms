@include('frontend.partials.header')

<div class="login-page">

    <div class="auth-container">
        <h2>Login</h2>

        @if(session('success'))
            <p class="success">{{ session('success') }}</p>
        @endif

        @if($errors->any())
            <div class="alert">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('frontend.user.loginMatch') }}">
            @csrf

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" required>
                <i id="togglePassword" class="far fa-eye toggle-password"></i>
            </div>

            <div class="auth-row single-item">
                <div class="forgot-password">
                    <a href="{{ route('frontend.user.forgot-password') }}">Forgot Password?</a>
                </div>
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="register-container">
            <p>
                Not registered?
                <a href="{{ route('frontend.user.register') }}">Register Now</a>
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('togglePassword');
            const pwd = document.getElementById('password');
            if (toggle && pwd) {
                toggle.addEventListener('click', function () {
                    const type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
                    pwd.setAttribute('type', type);
                    this.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>

    @include('frontend.partials.footer')