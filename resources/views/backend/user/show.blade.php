@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <div class="user-detail-card">
        <h3 class="section-title">User Detail</h3>
        <div class="user-detail-image-section">
            @if($user->image)
                <img src="{{ asset('uploads/users/' . $user->image) }}" alt="Profile Image" class="profile-image">
            @else
                <div class="user-detail-placeholder">No Image</div>
            @endif
        </div>
        <br>
        <hr>
        <br>

        <ul class="user-detail-list">
            <li><strong>Name:</strong> {{ $user->name }}</li>
            <li><strong>Email:</strong> {{ $user->email }}</li>
            <li><strong>Role:</strong> {{ ucfirst($user->role) }}</li>
            <li><strong>Department:</strong> {{ $user->department->name ?? $user->dept_name ?? 'N/A' }}</li>
            <li><strong>DOB:</strong> {{ $user->dob ?? '-' }}</li>
            <li><strong>Address:</strong> {{ $user->address ?? '-' }}</li>
            <li><strong>Gender:</strong> {{ ucfirst($user->gender) ?? '-' }}</li>
            <li><strong>Status:</strong> {{ $user->status ?? '-' }}</li>
            <li><strong>Phone:</strong> {{ $user->phone ?? '-' }}</li>

            @if($user->role == 'student')
                <li><strong>Batch:</strong> {{ $user->batch ?? '-' }}</li>
                <li><strong>Semester:</strong> {{ $user->semester ?? '-' }}</li>
            @endif

            <li><strong>Last Login:</strong>{{ $user->last_login_at ? $user->last_login_at->format('d M Y, h:i A') : ' -' }}</li>
            <li><strong>Joined:</strong> {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</li>
        </ul>
        <a href="{{ route('admin.user.index') }}" class="btn-back">Back</a>
    </div>
</div>

@include('backend.partials.footer')