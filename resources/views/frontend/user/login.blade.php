<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>

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
        <input type="password" name="password" required>
        <i class="far fa-eye toggle-password" id="togglePassword"></i>

        <button type="submit">Login</button>
    </form>

    <p style="text-align:center;">Not registered? <a href="{{ route('frontend.user.register') }}">Register Now</a></p>
</div>

</body>
</html>
