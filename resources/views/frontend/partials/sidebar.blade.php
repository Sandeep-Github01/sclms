<aside class="sidebar">
    {{-- <div>
        <span class="app-logo">Sandeep's App 😜</span>
    </div> --}}
    <div class="profile-section">
        <div>
            <img src="{{ asset('images/user.png') }}" class="profile-pic">
            <a href="{{ route('frontend.user.profile') }}">{{ Auth::user()->name }}</a>
        </div>
    </div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('frontend.user.dashboard') }}">🏠 Dashboard</a></li>
        <li><a href="{{ route('leave.create') }}">📝 Leave Apply</a></li>
        <li><a href="{{ route('leave.list') }}">📂 My Leaves</a></li>
    </ul>
</aside>

<!-- Overlay -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>