@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <div class="user-detail-card">
        <h3 class="section-title">User Detail</h3>

        <div class="user-detail-image-section">
            @if ($user->image)
                <img src="{{ asset('uploads/users/' . $user->image) }}" alt="Profile Image" class="profile-image">
            @else
                <div class="user-detail-placeholder">No Image</div>
            @endif
        </div>

        <br>
        <hr><br>

        <ul class="user-detail-list">
            <li><strong>Name:</strong> {{ $user->name }}</li>
            <li><strong>Email:</strong> {{ $user->email }}</li>
            <li><strong>Role:</strong> {{ ucfirst($user->role) }}</li>
            <li><strong>Department:</strong> {{ $user->department->name ?? ($user->dept_name ?? 'N/A') }}</li>
            <li><strong>DOB:</strong> {{ $user->dob ?? '-' }}</li>
            <li><strong>Address:</strong> {{ $user->address ?? '-' }}</li>
            <li><strong>Gender:</strong> {{ ucfirst($user->gender) ?? '-' }}</li>
            <li><strong>Phone:</strong> {{ $user->phone ?? '-' }}</li>

            @if ($user->role == 'student')
                <li><strong>Batch:</strong> {{ $user->batch ?? '-' }}</li>
                <li><strong>Semester:</strong> {{ $user->semester ?? '-' }}</li>
            @endif

            <li><strong>Last Login:</strong>
                {{ $user->last_login_at ? $user->last_login_at: ' -' }}</li>
            <li><strong>Joined:</strong> {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</li>
        </ul>

        <form action="{{ route('admin.user.updateStatus', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="status-select-wrapper">
                <label for="status">Status:</label>
                <select name="status" id="status" class="status-select" required>
                    <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn-table-view">Save</button>
        </form>

        <div class="user-actions" style="margin-top: 20px;">
            <a href="{{ route('admin.user.index') }}" class="btn-back">Back</a>
        </div>
    </div>
</div>

@include('backend.partials.footer')
