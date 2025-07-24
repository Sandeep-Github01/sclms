@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    <h2>Update Profile</h2>

    @if (session('popup'))
        <script>
            alert('Your profile will be updated after admin approves.');
        </script>
    @endif

    @if ($errors->any())
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('frontend.user.profileUpdate') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div style="margin-bottom: 10px;">
            <label>Name:</label><br>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>Email:</label><br>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>Current Image:</label><br>
            @if ($user->image)
                <img src="{{ asset('uploads/users/' . $user->image) }}" alt="Profile Image" width="120"
                    style="display: block; margin-bottom: 5px;">
            @else
                <p>No image uploaded yet.</p>
            @endif
            <label>Change Image:</label><br>
            <input type="file" name="image" accept="image/*">
        </div>

        <div style="margin-bottom: 10px;">
            <label>DOB (In AD):</label><br>
            <input type="date" name="dob" value="{{ old('dob', $user->dob) }}" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>Address:</label><br>
            <input type="text" name="address" value="{{ old('address', $user->address) }}" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>Gender:</label><br>
            <select name="gender" required>
                <option value="">Select Gender</option>
                <option value="male" {{ strtolower($user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ strtolower($user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                <option value="other" {{ strtolower($user->gender) == 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

        {{-- <div style="margin-bottom: 10px;">
            <label>Status:</label><br>
            <select name="status" required>
                <option value="">Select Status</option>
                <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $user->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div> --}}

        <div style="margin-bottom: 10px;">
            <label>Role:</label>
            <select name="role">
                <option value="">-- Select Role --</option>
                <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Student</option>
                <option value="teacher" {{ old('role', $user->role) == 'teacher' ? 'selected' : '' }}>Teacher</option>
            </select>
        </div>

        <div style="margin-bottom: 10px;">
            <label>Department:</label><br>
            <select name="dept_name" required>
                <option value="">-- Select Department --</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept }}" {{ $user->dept_name == $dept ? 'selected' : '' }}>
                        {{ $dept }}</option>
                @endforeach
            </select>
        </div>

        @if ($user->role == 'student')
            <div style="margin-bottom: 10px;">
                <label>Batch (In AD):</label><br>
                <input type="text" name="batch" value="{{ old('batch', $user->batch) }}" required>
            </div>
            <div style="margin-bottom: 10px;">
                <label for="semester">Semester:</label><br>
                <select name="semester" id="semester" required>
                    <option value="">-- Select Semester --</option>
                    @for ($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}"
                            {{ old('semester', $user->semester) == $i ? 'selected' : '' }}>
                            Semester {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>
        @endif

        <div style="margin-bottom: 10px;">
            <label>Phone:</label><br>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required maxlength="10">
        </div>

        <button type="submit"
            style="background-color: #007bff; color: #fff; padding: 10px 15px; border: none; border-radius: 4px;">
            Update Profile
        </button>
    </form>
</div>

@include('frontend.partials.footer')
