<aside class="sidebar">
    <div class="profile-section">
        <div>
            <a href="{{ route('admin.profile') }}" class="sidebar-nav" style="font-size: 18px;"><strong>{{ Auth::guard('admin')->user()->name }}</strong></a>
        </div>
    </div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('backend.dashboard') }}">🏠 Dashboard</a></li>

        <li>
            <a href="javascript:void(0);" class="dashboard-toggle">
                <span class="toggle-label">💻 Admin Works</span>
                <span class="toggle-icon">&gt;</span>
            </a>
            <ul class="sidebar-submenu dashboard-submenu">
                <li>
                    <a href="{{ route('admin.department.index') }}">🏫 Departments</a>
                </li>
                <li>
                    <a href="{{ route('admin.blackout.index') }}">🗓️ Blackout Periods</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:void(0);" class="dashboard-toggle">
                <span class="toggle-label">👥 Users</span>
                <span class="toggle-icon">&gt;</span>
            </a>
            <ul class="sidebar-submenu users-submenu">
                <li>
                    <a href="{{ route('admin.user.index') }}">📋 Users</a>
                </li>
                <li>
                    <a href="{{ route('admin.user.review_index') }}">📝 Review User</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="javascript:void(0);" class="dashboard-toggle">
                <span class="toggle-label">🏖️ Leaves</span>
                <span class="toggle-icon">&gt;</span>
            </a>
            <ul class="sidebar-submenu leaves-submenu">
                <li>
                    <a href="{{ route('admin.leaves.recent') }}">🕒 Recent Leaves</a>
                </li>
                <li>
                    <a href="{{ route('admin.leaves.index') }}">📑 Review Leaves</a>
                </li>
            </ul>
        </li>

    </ul>
</aside>