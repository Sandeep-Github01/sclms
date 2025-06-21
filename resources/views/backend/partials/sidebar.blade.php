<aside style="width: 220px; float: left; background: #eeeeee; height: 100vh; padding: 15px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="{{ asset('images/admin.png') }}" class="rounded-circle" style="width: 60px; height: 60px;">
        <div>{{ Auth::user()->name }}</div>
        <small>Admin</small>
    </div>
    <ul style="list-style: none; padding-left: 0;">
        <li><a href="{{ route('admin.user.index') }}">👥 View Users</a></li>
        <li><a href="#">📊 Reports</a></li>
        <li><a href="{{ route('logout') }}">🚪 Logout</a></li>
    </ul>
</aside>