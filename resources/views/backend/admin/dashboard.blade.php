@include('backend.partials.header')
@include('backend.partials.sidebar')

<div class="main-content">
    <h2>Welcome, {{ Auth::guard('admin')->user()->name ?? '' }}</h2>
    <p class="role-label">Role: Admin</p>

    <div class="summary-box">
        <h3>System Overview</h3>
        <ul>
            <li>Total Users: {{ $totalUsers }}</li>
            <li>Students Count: {{ $studentsCount }}</li>
            <li>Teachers Count: {{ $teachersCount }}</li>
            <li>Pending Leave Requests: {{ $pendingLeaves }}</li>
        </ul>
    </div>
</div>

@include('backend.partials.footer')
