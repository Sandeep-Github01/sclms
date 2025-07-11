@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <div class="user-detail-card">
        <h3 class="section-title">User Detail</h3>
        <ul class="user-detail-list">
            <li><strong>Name:</strong> {{ $user->name }}</li>
            <li><strong>Email:</strong> {{ $user->email }}</li>
            <li><strong>Role:</strong> {{ ucfirst($user->role) }}</li>
            <li><strong>Department:</strong> {{ $user->department->name ?? 'N/A' }}</li>
            <li><strong>Batch:</strong> {{ $user->batch ?? '-' }}</li>
            <li><strong>Joined:</strong> {{ $user->created_at->format('d M Y') }}</li>
        </ul>
        <a href="{{ route('admin.user.index') }}" class="btn-back">Back</a>
    </div>
</div>

@include('backend.partials.footer')
