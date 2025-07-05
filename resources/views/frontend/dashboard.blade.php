@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div class="main-content">
    {{-- User ko naam ra role --}}
    <h2>Welcome, {{ $user->name }}</h2>
    <p class="role-label">Role: {{ ucfirst($user->role) }}</p>

    {{-- Summary box --}}
    <div class="summary-box">
        <h3>Your Leave Summary</h3>
        <ul>
            <li>Total Leaves Applied: {{ $totalLeaves }}</li>
            <li>Pending Leaves: {{ $pendingLeaves }}</li>
            <li>
                Last Leave Status:
                @if($lastLeave)
                    {{ ucfirst($lastLeave->status) }}
                    ({{ $lastLeave->start_date }} to {{ $lastLeave->end_date }})
                @else
                    No records yet
                @endif
            </li>
        </ul>
    </div>
</div>

@include('frontend.partials.footer')