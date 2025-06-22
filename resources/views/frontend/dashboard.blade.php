@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    <h2>Welcome, {{ $user->name }}</h2>
    <p class="role-label">Role: {{ $user->role }}</p>

    <div class="summary-box">
        <h3>Your Leave Summary (Coming Soon)</h3>
        <ul>
            <li>Total Leaves Applied: --</li>
            <li>Pending Leaves: --</li>
            <li>Last Leave Status: --</li>
        </ul>
    </div>
</div>

@include('frontend.partials.footer')
