<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Smart College LMS</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body>
    <header class="admin-header">
        <div class="logo">SMART COLLEGE LEAVE MANAGEMENT SYSTEM</div>
        <div class="logout">
            <form method="POST" action="{{ route('frontend.user.logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>
    </header>
