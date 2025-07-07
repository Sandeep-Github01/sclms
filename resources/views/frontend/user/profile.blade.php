@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    
<div class="profile-container">
    <h2 class="profile-heading">My Profile</h2>

    <div class="profile-card">
        <div class="profile-image-section">
            @if($user->image)
                <img src="{{ asset('uploads/users/' . $user->image) }}" alt="Profile Image" class="profile-image">
            @else
                <div class="profile-placeholder">No Image</div>
            @endif
        </div>

        <ul class="profile-details">
            <li><span class="profile-label">Name:</span> {{ $user->name }}</li>
            <li><span class="profile-label">Email:</span> {{ $user->email }}</li>
            <li><span class="profile-label">Role:</span> {{ ucfirst($user->role) }}</li>
            <li><span class="profile-label">Department:</span> {{ $user->department->name ?? $user->dept_name ?? 'N/A' }}</li>
            <li><span class="profile-label">DOB:</span> {{ $user->dob }}</li>
            <li><span class="profile-label">Address:</span> {{ $user->address }}</li>
            <li><span class="profile-label">Gender:</span> {{ ucfirst($user->gender) }}</li>
            <li><span class="profile-label">Status:</span> {{ $user->status }}</li>
            <li><span class="profile-label">Phone:</span> {{ $user->phone }}</li>

            @if($user->role == 'student')
                <li><span class="profile-label">Batch:</span> {{ $user->batch }}</li>
                <li><span class="profile-label">Semester:</span> {{ $user->semester }}</li>
            @endif
        </ul>

        <div class="profile-actions">
            <a href="{{ route('frontend.user.profileEdit') }}" class="profile-edit-btn">Edit Profile</a>
        </div>
    </div>
</div>
</div>

@include('frontend.partials.footer')