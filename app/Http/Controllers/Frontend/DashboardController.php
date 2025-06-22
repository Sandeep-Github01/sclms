<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // User login bhayepaxi dashboard dekhaucha
    public function index()
    {
        $user = Auth::user();

        // Total leaves applied
        $totalLeaves = $user->leaveRequests()->count();

        // Pending leaves
        $pendingLeaves = $user->leaveRequests()
                              ->where('status', 'pending')
                              ->count();

        // Last leave status: sabai leaves bata latest start_date ko record
        $lastLeave = $user->leaveRequests()
                          ->orderBy('start_date', 'desc')
                          ->first();

        // Pass data view maa
        return view('frontend.dashboard', compact(
            'user',
            'totalLeaves',
            'pendingLeaves',
            'lastLeave'
        ));
    }
}
