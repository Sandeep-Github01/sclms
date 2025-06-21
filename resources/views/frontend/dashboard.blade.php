@include('frontend.partials.header')
@include('frontend.partials.sidebar')

<div style="margin-left: 230px; padding: 20px;">
    <h2>Welcome, {{ $user->name }}</h2>
    <p>Role: {{ $user->role }}</p>

    {{-- Yo thauma user-specific stats dekhauxa --}}
    {{-- e.g., total leaves, pending approvals, recent leave status --}}

    <div style="margin-top: 20px;">
        <h4>Your Leave Summary (Coming Soon)</h4>
        <ul>
            <li>Total Leaves Applied: --</li>
            <li>Pending Leaves: --</li>
            <li>Last Leave Status: --</li>
        </ul>
    </div>
</div>

@include('frontend.partials.footer')
