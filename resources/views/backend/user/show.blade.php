{{-- @include('backend.partials.header')
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

            @if ($user->role == 'student')
                <li><strong>Batch:</strong> {{ $user->batch ?? '-' }}</li>
                <li><strong>Semester:</strong> {{ $user->semester ?? '-' }}</li>
            @endif

            <li><strong>Last Login:</strong>{{ $user->last_login_at ? $user->last_login_at->format('d M Y, h:i A') : ' -' }}</li>
            <li><strong>Joined:</strong> {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</li>
        </ul>
        <a href="{{ route('admin.user.index') }}" class="btn-back">Back</a>
    </div>
</div>

@include('backend.partials.footer') --}}

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
        <hr>
        <br>

        <ul class="user-detail-list">
            <li><strong>Name:</strong> {{ $user->name }}</li>
            <li><strong>Email:</strong> {{ $user->email }}</li>
            <li><strong>Role:</strong> {{ ucfirst($user->role) }}</li>
            <li><strong>Department:</strong> {{ $user->department->name ?? ($user->dept_name ?? 'N/A') }}</li>
            <li><strong>DOB:</strong> {{ $user->dob ?? '-' }}</li>
            <li><strong>Address:</strong> {{ $user->address ?? '-' }}</li>
            <li><strong>Gender:</strong> {{ ucfirst($user->gender) ?? '-' }}</li>
            <li>
                <strong>Status:</strong>
                <span class="status-badge status-{{ $user->status }}">
                    {{ ucfirst($user->status) ?? '-' }}
                </span>
            </li>
            <li><strong>Phone:</strong> {{ $user->phone ?? '-' }}</li>

            @if ($user->role == 'student')
                <li><strong>Batch:</strong> {{ $user->batch ?? '-' }}</li>
                <li><strong>Semester:</strong> {{ $user->semester ?? '-' }}</li>
            @endif

            <li><strong>Last
                    Login:</strong>{{ $user->last_login_at ? $user->last_login_at->format('d M Y, h:i A') : ' -' }}
            </li>
            <li><strong>Joined:</strong> {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</li>
        </ul>

        <div class="user-actions">
            <form action="{{ route('admin.user.toggleStatus', $user) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit"
                    class="btn-status {{ $user->status === 'active' ? 'btn-deactivate' : 'btn-activate' }}"
                    onclick="return confirm('Are you sure you want to {{ $user->status === 'active' ? 'deactivate' : 'activate' }} this user?')">
                    {{ $user->status === 'active' ? 'Deactivate User' : 'Activate User' }}
                </button>
            </form>

            <a href="{{ route('admin.user.index') }}" class="btn-back">Back</a>
        </div>
    </div>
</div>

<style>
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-active {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-inactive {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .user-actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .btn-status {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .btn-activate {
        background-color: #28a745;
        color: white;
    }

    .btn-activate:hover {
        background-color: #218838;
    }

    .btn-deactivate {
        background-color: #dc3545;
        color: white;
    }

    .btn-deactivate:hover {
        background-color: #c82333;
    }

    .btn-back {
        padding: 8px 16px;
        background-color: #6c757d;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .btn-back:hover {
        background-color: #5a6268;
    }
</style>

@include('backend.partials.footer')
