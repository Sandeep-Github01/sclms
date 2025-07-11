@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>User Detail</h2>

    <ul>
        <li><strong>Name:</strong> {{ $user->name }}</li>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Role:</strong> {{ ucfirst($user->role) }}</li>
        <li><strong>Department:</strong> {{ $user->department->name ?? 'N/A' }}</li>
        <li><strong>Batch:</strong> {{ $user->batch ?? '-' }}</li>
        <li><strong>Joined:</strong> {{ $user->created_at->format('d M Y') }}</li>
    </ul>
</div>

@include('backend.partials.footer')
