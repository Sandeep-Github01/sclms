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

            <label>Password:</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" required>
                <i id="togglePassword1" class="far fa-eye toggle-password"></i>
            </div>

            <label>Confirm Password:</label>
            <div class="password-wrapper">
                <input type="password" name="password_confirmation" id="password2" required>
                <i id="togglePassword2" class="far fa-eye toggle-password"></i>
            </div>

            <button type="submit">Reset Password</button>
        </form>

        <div class="register-container">
            <p>
                <a href="{{ route('admin.login') }}">Back to Login</a>
            </p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function setupToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);

            if (toggle && input) {
                toggle.addEventListener('click', function () {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.classList.toggle('fa-eye-slash');
                });
            }
        }

        setupToggle('togglePassword1', 'password');
        setupToggle('togglePassword2', 'password2');
    });
</script>

@include('backend.partials.footer')