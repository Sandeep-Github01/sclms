@include('backend.partials.header')

<div class="login-page">

    <div class="auth-container">
        <h2>Admin Login</h2>

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

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf

            <div>
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}">
                @error('email') <small>{{ $message }}</small> @enderror
            </div>

            <div>
                <label>Password:</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" required>
                    <i id="togglePassword" class="far fa-eye toggle-password"></i>
                    @error('password') <small>{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="auth-row single-item">
                <div class="forgot-password">
                    <a href="{{ route('admin.forgot-password') }}">Forgot Password?</a>
                </div>
            </div>

            <button type="submit">Login</button>
        </form>

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

    @include('backend.partials.footer')