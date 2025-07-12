<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="login-page">

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
                <label>Password</label>
                <input type="password" name="password">
                @error('password') <small>{{ $message }}</small> @enderror
            </div>

            <button type="submit">Login</button>
        </form>

        {{-- <p style="text-align:center;">
            <a href="{{ route('backend.admin.forgot-password') }}">Forgot Password?</a>
        </p> --}}

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

</body>

</html>