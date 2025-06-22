<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart College Leave Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Link to CSS -->
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>
    <header class="main-header">
        <div class="header-left">
            <span class="app-logo">YourAppName</span> {{-- You can change "YourAppName" later --}}
        </div>
        <div class="header-center">
            SMART COLLEGE LEAVE MANAGEMENT SYSTEM
        </div>
       <div class="header-right">
    <form id="logout-form" method="POST" action="{{ route('frontend.user.logout') }}" style="display:none;">
    @csrf
</form>
<a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
    Logout
</a>
</div>

    </header>
