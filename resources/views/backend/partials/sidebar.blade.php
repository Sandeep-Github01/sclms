<aside class="sidebar">
    <div class="profile-section">
        <div>
            <img src="{{ asset('images/user.png') }}" class="profile-pic">
            <a href="{{ route('admin.profile') }}">{{ Auth::guard('admin')->user()->name }}</a>
        </div>
    </div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('backend.dashboard') }}">🏠 Dashboard</a></li>
        <li><a href="{{ route('admin.user.index') }}">👥 Manage Users</a></li>
        <li><a href="{{ route('admin.profile') }}">⚙️ Profile</a></li>
    </ul>
</aside>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>
