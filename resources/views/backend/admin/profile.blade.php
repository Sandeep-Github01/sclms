@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="content">
    <h1>Admin Profile</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.profile.update') }}" method="POST">
        @csrf

        <div>
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $admin->name) }}">
            @error('name') <small>{{ $message }}</small> @enderror
        </div>

        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $admin->email) }}">
            @error('email') <small>{{ $message }}</small> @enderror
        </div>

        <div>
            <label>New Password</label>
            <input type="password" name="password">
            @error('password') <small>{{ $message }}</small> @enderror
        </div>

        <div>
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation">
        </div>

        <button type="submit">Update Profile</button>
    </form>
</div>

@include('backend.partials.footer')
