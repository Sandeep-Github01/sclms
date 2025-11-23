<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\LeaveCredit;
use App\Models\Approval;
use App\Models\Admin; // Added for consistency
use Illuminate\Support\Facades\Auth; // Modern facade import
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeaveDecisionMail;

class LeaveController extends Controller
{
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

    public function show($id)
    {
        $leave = LeaveRequest::with(['user', 'leaveType', 'department', 'approval', 'approval.approver'])
            ->findOrFail($id);

        return view('backend.AdminWorks.leaves.show', compact('leave'));
    }

    public function index()
    {
        $leaveRequests = LeaveRequest::where('status', 'pending')
            ->where('review_type', 'manual')
            ->with(['user', 'leaveType', 'department'])
            ->orderBy('created_at', 'asc')
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

    public function reviewLeave($id)
    {
        $leave = LeaveRequest::with(['user', 'leaveType', 'department'])
            ->where('id', $id)
            ->where('status', 'pending')
            ->where('review_type', 'manual')
            ->firstOrFail();

        $leaveCredit = LeaveCredit::where('user_id', $leave->user_id)
            ->where('type_id', $leave->type_id)
            ->first();

        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);
        $days = $start->diffInDays($end) + 1;

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

        $leave->update([
            'status' => $decision,
            'status_note' => $comment ?: "Manually {$decision} by admin.",
        ]);

        Approval::create([
            'leave_request_id' => $leave->id,
            'approved_by' => $admin->id,
            'status' => $decision,
            'comment' => $comment,
        ]);

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

        // Send decision mail to user
        Mail::to($leave->user->email)->send(new LeaveDecisionMail($leave));

        $message = $decision === 'approved'
            ? 'Leave application has been approved successfully.'
            : 'Leave application has been rejected.';

        return redirect()->route('admin.leaves.index')
            ->with('success', $message);
    }

    // public function markAbuse(Request $request, $id)
    // {
    //     $leave = LeaveRequest::findOrFail($id);
    //     $reason = $request->input('reason') ?? 'admin_flagged_abuse';

    //     // Use explicit guard for admin ID
    //     $adminId = Auth::guard('admin')->id();

    //     $penalty = app(\App\Services\Leave\PenaltyService::class);
    //     $res = $penalty->markAbuse($leave, $reason, null, 'admin', $adminId);

    //     // Store admin comment in notes
    //     $leave->notes = trim(($leave->notes ?? '') . "\nAdmin comment: " . ($request->input('comment') ?? ''));
    //     $leave->save();

    //     return redirect()->back()->with('success', 'Leave marked as abuse and penalty applied.');
    // }
    public function markAbuse(Request $request, $id)
    {
        // Debug what we're receiving
        \Log::info('markAbuse called', [
            'id' => $id,
            'reason_from_input' => $request->input('reason'),
            'all_request_data' => $request->all()
        ]);

        $leave = LeaveRequest::findOrFail($id);

        \Log::info('Leave found', [
            'leave_id' => $leave->id,
            'leave_exists' => $leave->exists,
            'leave_user_id' => $leave->user_id
        ]);

        $reason = $request->input('reason') ?? 'admin_flagged_abuse';
        $adminId = Auth::guard('admin')->id();

        \Log::info('About to call PenaltyService', [
            'leave_id' => $leave->id,
            'reason' => $reason,
            'admin_id' => $adminId
        ]);

        $penalty = app(\App\Services\Leave\PenaltyService::class);
        $res = $penalty->markAbuse($leave, $reason, null, 'admin', $adminId);

        \Log::info('PenaltyService result', ['result' => $res]);

        // Store admin comment in notes
        $leave->notes = trim(($leave->notes ?? '') . "\nAdmin comment: " . ($request->input('comment') ?? ''));
        $leave->save();

        return redirect()->route('admin.leaves.index')
            ->with('success', 'Leave marked as abuse and penalty applied.');
    }
}
