<aside class="sidebar">
    <div class="profile-section">
        <div>
            <a href="{{ route('admin.profile') }}">{{ Auth::guard('admin')->user()->name }}</a>
        </div>
    </div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('backend.dashboard') }}">🏠 Dashboard</a></li>
        <li><a href="{{ route('admin.user.index') }}">Users</a></li>
        <li><a href="{{ route('admin.recent_leaves') }}">Recent Leaves</a></li>
        <li>
            <a href="javascript:void(0);" class="admin-works-toggle">
                <span class="toggle-label">Admin Works</span>
                <span class="toggle-icon">&gt;</span>
            </a>
            <ul class="sidebar-submenu admin-works-submenu">
                <li>
                    <a href="{{ route('admin.department.index') }}">Departments</a>
                </li>
                <li>
                    <a href="{{ route('admin.blackout.index') }}">Blackout Periods</a>
                </li>
                <li>
                    <a href="{{ route('admin.user.review_index') }}">Review User</a>
                </li>
                <li>
                    <a href="{{ route('admin.leaves.index') }}">Review Leaves</a>
                </li>
            </ul>
        </li>
    </ul>
</aside>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>
