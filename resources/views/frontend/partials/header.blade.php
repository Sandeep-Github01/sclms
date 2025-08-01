<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Smart College Leave Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Link to CSS -->
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

</head>

<body>
    <header class="main-header">
        <div class="header-left">
            <button id="sidebarToggle" class="hamburger-btn">
                <i class="fas fa-bars"></i>
            </button>
            <span class="app-logo">Sandeep's App 😜</span>
        </div>

        <div class="header-center">
            SMART COLLEGE LEAVE MANAGEMENT SYSTEM
        </div>

        <div class="header-right">
            @auth
                <form id="logout-form" method="POST" action="{{ route('frontend.user.logout') }}" style="display: none;">
                    @csrf
                </form>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            @endauth
        </div>

    </header>