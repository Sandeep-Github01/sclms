@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <div class="user-detail-card">
        <h3 class="section-title">Review Profile Update</h3>

        <div class="user-detail-image-section">
            @if($user->image)
                <img src="{{ asset('uploads/users/' . $user->image) }}" alt="Profile Image" class="profile-image">
            @else
                <div class="user-detail-placeholder">No Image</div>
            @endif
        </div>

        <br><hr><br>

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

            <li><strong>Last Login:</strong> {{ $user->last_login_at ? $user->last_login_at->format('d M Y, h:i A') : '-' }}</li>
            <li><strong>Joined:</strong> {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</li>
        </ul>

        <form action="{{ route('admin.user.review', $user->id) }}" method="POST" style="margin-top: 20px;">
            @csrf
            @method('PUT')

            <label for="reason"><strong>Decline Reason (if applicable):</strong></label><br>
            <textarea name="reason" id="reason" rows="4" style="width: 100%; margin-bottom: 15px;"></textarea>

            <button type="submit" name="action" value="approve"
                style="background-color: #28a745; color: #fff; padding: 10px 15px; border: none; border-radius: 4px; margin-right: 10px;">
                Approve
            </button>

            <button type="submit" name="action" value="decline"
                style="background-color: #dc3545; color: #fff; padding: 10px 15px; border: none; border-radius: 4px;">
                Decline
            </button>
        </form>

        <a href="{{ route('admin.user.index') }}" class="btn-back" style="margin-top: 20px; display: inline-block;">Back</a>
    </div>
</div>

@include('backend.partials.footer')
