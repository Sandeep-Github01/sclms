<aside class="sidebar">

    <ul class="sidebar-nav">
        <li>
            <div class="profile-section">
                <div class="profile-info">
                    <img src="{{ Auth::user()->image ? asset('uploads/users/' . Auth::user()->image) : asset('images/user.png') }}"
                        class="profile-pic">
                    <a href="{{ route('frontend.user.profile') }}">{{ Auth::user()->name }}</a>
                </div>
            </div>
        </li>
        <li><a href="{{ route('frontend.user.dashboard') }}">🏠 Dashboard</a></li>
        <li><a href="{{ route('leave.create') }}">📝 Leave Apply</a></li>
        <li><a href="{{ route('leave.list') }}">📂 My Leaves</a></li>
    </ul>

</aside>

<!-- Overlay -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>