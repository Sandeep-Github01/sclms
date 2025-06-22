<div style="width: 400px; margin: 50px auto;">
    <h2>Register New Account</h2>

    @if($errors->any())
        <div style="color: red;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('frontend.user.registerSave') }}" method="POST">
        @csrf

        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Confirm Password:</label><br>
        <input type="password" name="password_confirmation" required><br><br>

        <label>Role:</label><br>
        <select name="role" required>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
        </select><br><br>

        <label>Department ID:</label><br>
        <input type="number" name="department_id" required><br><br>

        <button type="submit">Register</button>
    </form>

    <p>Already registered? <a href="{{ route('frontend.user.login') }}">Login here</a></p>
</div>
