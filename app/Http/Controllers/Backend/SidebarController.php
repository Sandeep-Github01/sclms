<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;

class SidebarController extends Controller
{
    public function leavesByRole()
    {
        $leaveRequests = LeaveRequest::with(['user', 'leaveType'])->get();

        $grouped = $leaveRequests->groupBy(function ($leave) {
            return $leave->user->role ?? 'Unknown';
        });

        $students = $grouped->get('student', collect());
        $teachers = $grouped->get('teacher', collect());

        return view('backend.AdminWorks.recent_leaves', [
            'students' => $students,
            'teachers' => $teachers,
        ]);
    }


    public function manualReview()
    {
        $pendingLeaves = LeaveRequest::where('status', 'pending')
            ->where('review_type', 'manual')
            ->with(['user', 'leaveType'])
            ->get();

        return view('backend.AdminWorks.review_leave', [
            'pendingLeaves' => $pendingLeaves,
        ]);
    }
}
