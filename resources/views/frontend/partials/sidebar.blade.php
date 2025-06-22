<aside class="sidebar">
    {{-- <div class="company-logo">
        <img src="{{ asset('images/logo.png') }}" alt="App Logo">
    </div>
    <ul>
        <li>
        </li>
        <li>
        </li>
        <li>
        </li>
    </ul> --}}
    <div class="profile-section">
        <img src="{{ asset('images/user.png') }}" class="profile-pic">
        <div>{{ Auth::user()->name }}</div>
        {{-- <small>Profile</small> --}}
    </div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('dashboard') }}">🏠 Dashboard</a></li>
        <li><a href="{{ route('frontend.user.profile') }}">👤 My Profile</a></li>
        <li><a href="#">📝 Leave Apply</a></li>
        <li><a href="#">📂 My Leaves</a></li>
        <li><a href="{{ route('user.logout') }}">🚪 Logout</a></li>
    </ul>
</aside>
