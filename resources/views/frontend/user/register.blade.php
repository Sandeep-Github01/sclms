<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>

<div class="auth-container">
    <h2>Register New Account</h2>

    @if($errors->any())
        <div class="alert">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('frontend.user.registerSave') }}" method="POST">
        @csrf

        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>
        <i class="far fa-eye toggle-password" id="togglePassword"></i>

        <label>Confirm Password:</label>
        <input type="password" name="password_confirmation" required>
        <i class="far fa-eye toggle-password" id="togglePassword"></i>

        <label>Role:</label>
        <select name="role" required>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
        </select>

        <label>Department ID:</label>
        <input type="number" name="department_id" required>

        <button type="submit">Register</button>
    </form>

    <p style="text-align:center;">Already registered? <a href="{{ route('frontend.user.login') }}">Login here</a></p>
</div>

</body>
</html>
