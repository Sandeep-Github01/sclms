<aside style="width: 220px; float: left; background: #f0f0f0; height: 100vh; padding: 15px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="{{ asset('images/user.png') }}" class="rounded-circle" style="width: 60px; height: 60px;">
        <div>{{ Auth::user()->name }}</div>
        <small>Profile</small>
    </div>
    <ul style="list-style: none; padding-left: 0;">
        <li><a href="{{ route('dashboard') }}">🏠 Dashboard</a></li>
        <li><a href="{{ route('frontend.user.profile') }}">👤 My Profile</a></li>
        <li><a href="#">📝 Leave Apply</a></li>
        <li><a href="#">📂 My Leaves</a></li>
        <li><a href="{{ route('user.logout') }}">🚪 Logout</a></li>
    </ul>
</aside>