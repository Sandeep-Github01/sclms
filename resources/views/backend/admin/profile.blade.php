@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">

    <div class="profile-container">
        <h2 class="profile-heading">Admin Profile</h2>

        <div class="profile-card">
            <div class="profile-image-section">
                @if($admin->image)
                    <img src="{{ asset('uploads/admins/' . $admin->image) }}" alt="Profile Image" class="profile-image">
                @else
                    <div class="profile-placeholder">No Image</div>
                @endif
            </div>

            <ul class="profile-details">
                <li><span class="profile-label">Name:</span> {{ $admin->name }}</li>
                <li><span class="profile-label">Email:</span> {{ $admin->email }}</li>
                <li><span class="profile-label">Role:</span> {{ ucfirst($admin->role) }}</li>
                <li><span class="profile-label">Department:</span> {{ $admin->department->name ?? $admin->dept_name ?? 'N/A' }}</li>
                <li><span class="profile-label">DOB:</span> {{ $admin->dob }}</li>
                <li><span class="profile-label">Address:</span> {{ $admin->address }}</li>
                <li><span class="profile-label">Gender:</span> {{ ucfirst($admin->gender) }}</li>
                <li><span class="profile-label">Status:</span> {{ $admin->status }}</li>
                <li><span class="profile-label">Phone:</span> {{ $admin->phone }}</li>
            </ul>

            <div class="profile-actions">
                <a href="{{ route('backend.admin.profileEdit') }}" class="profile-edit-btn">Edit Profile</a>
            </div>
        </div>
    </div>

</div>

@include('backend.partials.footer')
