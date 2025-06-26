<aside class="admin-sidebar">
    <div class="admin-profile">
        <img src="{{ asset('images/admin.png') }}" class="admin-avatar" alt="Admin Image">
        <div class="admin-name">{{ Auth::user()->name }}</div>
        <small class="admin-role">Admin</small>
    </div>

    <ul class="admin-nav">
        <li><a href="{{ route('admin.user.index') }}">👥 View Users</a></li>
        <li><a href="#">📊 Reports</a></li>
        <li>
            <form method="POST" action="{{ route('frontend.user.logout') }}">
                @csrf
                <button type="submit" class="logout-button">🚪 Logout</button>
            </form>
        </li>
    </ul>
</aside>
