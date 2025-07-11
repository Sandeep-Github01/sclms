@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>My Profile</h2>

    @if(session('success'))
        <p class="apply-success">{{ session('success') }}</p>
    @endif

    <form method="POST" action="{{ route('admin.profile.update') }}">
        @csrf

        <div class="form-group">
            <label>Name:</label>
            <input name="name" type="text" value="{{ old('name', $admin->name) }}">
            @error('name')
                <p class="apply-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input name="email" type="email" value="{{ old('email', $admin->email) }}">
            @error('email')
                <p class="apply-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label>New Password:</label>
            <input id="password" name="password" type="password" placeholder="Leave blank to keep current password">
            @error('password')
                <p class="apply-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label>Confirm Password:</label>
            <input name="password_confirmation" type="password">
        </div>

        <button type="submit" class="btn">Update Profile</button>
    </form>
</div>

@include('backend.partials.footer')
