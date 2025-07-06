@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    <h2>My Profile</h2>
    <ul>
        <li><strong>Name:</strong> {{ $user->name }}</li>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Role:</strong> {{ $user->role }}</li>
        <li><strong>Department:</strong> {{ $user->department->name ?? 'N/A' }}</li>
    </ul>
</div>

@include('frontend.partials.footer')