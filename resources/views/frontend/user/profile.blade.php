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
                <li><span class="profile-label">Department:</span>
                    {{ $user->department->name ?? $user->dept_name ?? 'N/A' }}</li>
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

    <div class="profile-container"
        style="max-width:600px;margin:25px auto;padding:25px;background:#fff;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.08);font-family:Arial,Helvetica,sans-serif;">

        <h2 style="margin:0 0 20px 0;font-size:1.5em;color:#333;text-align:center;">Penalty Status</h2>

        {{-- Current Points section --}}
        <h3 style="margin:0 0 8px 0;font-size:1.1em;color:#555;">Current Points</h3>
        <p style="margin:0 0 20px 0;font-size:1.8em;font-weight:bold;color:#d9534f;">{{ Auth::user()->penalty_points }}</p>

        <hr style="border:none;border-top:1px solid #e5e5e5;margin:15px 0;">

        {{-- Block notice --}}
        @if(Auth::user()->leave_blocked_until)
            <div style="padding:12px 15px;background:#f8d7da;color:#721c24;border-radius:4px;margin-bottom:15px;">
                Leave privileges blocked until:
                {{ Carbon\Carbon::parse(Auth::user()->leave_blocked_until)->format('M d, Y') }}
            </div>
        @elseif(Auth::user()->penalty_points >= 10)
            <div style="padding:12px 15px;background:#fff3cd;color:#856404;border-radius:4px;margin-bottom:15px;">
                Warning: High penalty points. All future leaves require manual review.
            </div>
        @endif

        {{-- Reasons section --}}
        <h3 style="margin:20px 0 8px 0;font-size:1.1em;color:#555;">Why did I get penalty points?</h3>
        <details style="cursor:pointer;">
            <summary style="color:#337ab7;font-weight:bold;">Show reasons</summary>
            <ul style="margin:8px 0 0 20px;padding:0;">
                <li>Missing document: +1 point</li>
                <li>Unapproved absence: +3 points</li>
                <li>Fraud detection: +4-8 points</li>
            </ul>
        </details>
    </div>

</div>

@include('frontend.partials.footer')
