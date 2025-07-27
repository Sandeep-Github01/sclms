<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\LeaveCredit;
use App\Models\Approval;
use Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{
    // 1. Show all recent leaves (approved/rejected) - for recent_leaves.blade
    public function recentLeaves()
    {
        $leaveRequests = LeaveRequest::with(['user', 'leaveType', 'department'])
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = $leaveRequests->groupBy(function ($leave) {
            return $leave->user->role ?? 'Unknown';
        });

        $students = $grouped->get('student', collect());
        $teachers = $grouped->get('teacher', collect());

        return view('backend.AdminWorks.leaves.recent_leaves', [
            'students' => $students,
            'teachers' => $teachers,
        ]);
    }

    // 2. Show specific leave details - for show.blade
    public function show($id)
    {
        $leave = LeaveRequest::with(['user', 'leaveType', 'department', 'approval', 'approval.approver'])
            ->findOrFail($id);

        return view('backend.AdminWorks.leaves.show', compact('leave'));
    }

    // 3. Show pending manual review leaves - for index.blade
    public function index()
    {
        $leaveRequests = LeaveRequest::where('status', 'pending')
            ->where('review_type', 'manual')
            ->with(['user', 'leaveType', 'department'])
            ->orderBy('created_at', 'desc')
            ->get();

        $grouped = $leaveRequests->groupBy(function ($leave) {
            return $leave->user->role ?? 'Unknown';
        });

        $students = $grouped->get('student', collect());
        $teachers = $grouped->get('teacher', collect());

        return view('backend.AdminWorks.leaves.index', [
            'students' => $students,
            'teachers' => $teachers,
        ]);
    }

    // 4. Show review form for specific leave - for review_leave.blade
    public function reviewLeave($id)
    {
        $leave = LeaveRequest::with(['user', 'leaveType', 'department'])
            ->where('id', $id)
            ->where('status', 'pending')
            ->where('review_type', 'manual')
            ->firstOrFail();

        // Get additional info for review
        $leaveCredit = LeaveCredit::where('user_id', $leave->user_id)
            ->where('type_id', $leave->type_id)
            ->first();

        // Calculate duration
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);
        $days = $start->diffInDays($end) + 1;

        // Get recent leaves for this user
        $recentLeaves = LeaveRequest::where('user_id', $leave->user_id)
            ->where('id', '!=', $leave->id)
            ->where('status', 'approved')
            ->whereBetween('start_date', [
                Carbon::now()->subDays(30)->toDateString(),
                Carbon::now()->toDateString()
            ])
            ->with('leaveType')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('backend.AdminWorks.leaves.review_leave', compact('leave', 'leaveCredit', 'days', 'recentLeaves'));
    }

    // 5. Process admin decision (approve/reject)
    public function processDecision(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:1000',
        ]);

        $leave = LeaveRequest::where('id', $id)
            ->where('status', 'pending')
            ->where('review_type', 'manual')
            ->firstOrFail();

        $admin = Auth::guard('admin')->user();
        $decision = $request->decision;
        $comment = $request->comment;

        // Update leave status
        $leave->update([
            'status' => $decision,
            'status_note' => $comment ?: "Manually {$decision} by admin.",
        ]);

        // Create approval record
        Approval::create([
            'leave_request_id' => $leave->id,
            'approved_by' => $admin->id,
            'status' => $decision,
            'comment' => $comment,
        ]);

        // If approved, deduct leave credits
        if ($decision === 'approved') {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            $duration = $start->diffInDays($end) + 1;

            $leaveCredit = LeaveCredit::where('user_id', $leave->user_id)
                ->where('type_id', $leave->type_id)
                ->first();

            if ($leaveCredit) {
                $leaveCredit->update([
                    'remaining_days' => max(0, $leaveCredit->remaining_days - $duration)
                ]);
            }
        }

        $message = $decision === 'approved' 
            ? 'Leave application has been approved successfully.' 
            : 'Leave application has been rejected.';

        return redirect()->route('admin.leaves.index')
            ->with('success', $message);
    }
}