@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div style="margin-left: 230px; padding: 20px;">
    <h2>Update Profile</h2>

    @if(session('popup'))
        <script>alert('Your profile will be updated after admin approves.');</script>
    @endif

    @if ($errors->any())
        <div class="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('frontend.user.profileUpdate') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <label>Name:</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}">

        <label>Email:</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}">

        <label>Image:</label>
        <input type="file" name="image">

        <label>DOB:</label>
        <input type="date" name="dob" value="{{ old('dob', $user->dob) }}">

        <label>Address:</label>
        <input type="text" name="address" value="{{ old('address', $user->address) }}">

        <label>Gender:</label>
        <select name="gender">
            <option value="">Select Gender</option>
            <option value="Male" {{ $user->gender == 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ $user->gender == 'Female' ? 'selected' : '' }}>Female</option>
        </select>

        <label>Status:</label>
        <select name="status">
            <option value="Active" {{ $user->status == 'Active' ? 'selected' : '' }}>Active</option>
            <option value="Inactive" {{ $user->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
        </select>

        <label>Department:</label>
        <select name="dept_name">
            <option value="">-- Select Department --</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ $user->dept_name == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>

        @if($user->role == 'student')
            <label>Batch:</label>
            <input type="text" name="batch" value="{{ old('batch', $user->batch) }}">

            <label>Semester:</label>
            <input type="text" name="semester" value="{{ old('semester', $user->semester) }}">
        @endif

        <label>Phone:</label>
        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">

        <button type="submit">Update Profile</button>
    </form>
</div>

@include('frontend.partials.footer')
