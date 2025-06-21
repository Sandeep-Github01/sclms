@include('backend.partials.header')
@include('backend.partials.sidebar')

<div style="margin-left: 230px; padding: 20px;">
    <h2>User Detail</h2>

    <ul>
        <li><strong>Name:</strong> {{ $user->name }}</li>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Role:</strong> {{ $user->role }}</li>
        <li><strong>Department:</strong> {{ $user->department->name ?? 'N/A' }}</li>
        {{-- Batch or extra info cha bhane add garna sakincha --}}
    </ul>
</div>

@include('backend.partials.footer')
