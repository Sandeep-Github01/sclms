@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div style="margin-left: 230px; padding: 20px;">
    <h2>Register New Account</h2>

    {{-- Simple form for student/teacher register garna --}}
    <form action="{{ route('register.submit') }}" method="POST">
        @csrf

        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Role:</label><br>
        <select name="role" required>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
        </select><br><br>

        <label>Department ID:</label><br>
        <input type="number" name="department_id" required><br><br>

        <button type="submit">Register</button>
    </form>
</div>

@include('frontend.partials.footer')