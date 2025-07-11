<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LeaveRequest;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Count all users
        $totalUsers = User::count();

        // Count students
        $studentsCount = User::where('role', 'student')->count();

        // Count teachers
        $teachersCount = User::where('role', 'teacher')->count();

        // Count pending leave requests
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();

        // Pass to view
        return view('backend.admin.dashboard', compact(
            'totalUsers',
            'studentsCount',
            'teachersCount',
            'pendingLeaves'
        ));
    }
}
