<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalLeaves = $user->leaveRequests()->count();

        $pendingLeaves = $user->leaveRequests()
            ->where('status', 'pending')
            ->count();

        $lastLeave = $user->leaveRequests()
            ->orderBy('start_date', 'desc')
            ->first();

        return view('frontend.dashboard', compact(
            'user',
            'totalLeaves',
            'pendingLeaves',
            'lastLeave'
        ));
    }
}
