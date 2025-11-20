<?php

namespace App\Services\Leave;

use App\Mail\LeaveManualReviewMail;
use App\Mail\LeaveSubmittedMail;
use App\Models\Admin;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LeaveExecutionService
{
    /**
     * $request - incoming Request
     * $validation - output from LeaveValidationService
     * $conflict - output from LeaveConflictService
     * $credit   - output from LeaveCreditService
     *
     * Returns:
     * [
     *   'success' => true|false,
     *   'message' => null|'error message',
     *   'leave' => LeaveRequest|null,
     *   'steps' => [...]
     * ]
     */
    public function createLeave($request, $validation, $conflict, $creditOutput)
    {
        $user = Auth::user();

        // Start by merging steps and score
        $steps = $validation['steps'] ?? [];
        $steps = array_merge($steps, $conflict['steps'] ?? [], $creditOutput['steps'] ?? []);
        $score = $creditOutput['score'] ?? ($validation['score'] ?? 0);

        $leaveType = $validation['leaveType'];
        $department = $validation['department'];
        $start = $validation['start'];
        $end = $validation['end'];
        $days = $validation['days'];
        $filePath = $validation['filePath'] ?? null;
        $fraudReasons = $validation['fraudReasons'] ?? [];
        $fraudScore = $validation['fraudScore'] ?? 0;
        $isManual = $validation['isManual'] ?? false;

        // If conflict service forced manual due department load
        if (!empty($conflict['force_manual_due_department_load'])) {
            $isManual = true;
        }

        // -------------------------
        // Build base data array
        // -------------------------
        $data = [
            'user_id'       => $user->id,
            'type_id'       => $leaveType->id,
            'department_id' => $department->id,
            'start_date'    => $start->toDateString(),
            'end_date'      => $end->toDateString(),
            'reason'        => $request->reason,
            'file_path'     => $filePath,
            'status'        => 'pending',
            'review_type'   => 'auto',
            'final_score'   => null,
            'status_note'   => null,
            'role'          => $user->role,
            'semester'      => $user->semester,
        ];

        // If earlier fraud forced manual
        if ($isManual) {
            $data['review_type'] = 'manual';
            $leave = LeaveRequest::create($data);

            // Notify user
            Mail::to($user->email)->send(new LeaveSubmittedMail($leave));

            // Notify admin
            $admin = Admin::first();
            if ($admin) {
                Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
            }

            $steps[] = ['text' => "Leave submitted for manual review.", 'score' => $score, 'type' => 'success'];
            session(['leave_steps' => $steps]);

            return [
                'success' => true,
                'message' => null,
                'leave' => $leave,
                'steps' => $steps,
            ];
        }

        // -------------------------
        // Auto evaluation: already applied credit & recentCounts etc in creditOutput
        // -------------------------
        $credit = $creditOutput['credit'] ?? null;
        $recentCount = $creditOutput['recentCount'] ?? 0;
        $doc_present = $creditOutput['doc_present'] ?? (!empty($filePath) ? 1 : 0);

        // -------------------------
        // Blackout re-check: conflict service already did this and would have returned false earlier.
        // -------------------------

        // -------------------------
        // Department load, conflicts were handled in conflict service; now compute final risk/conflict vars for model.
        // -------------------------
        $conflicts = $conflict['conflicts'] ?? 0;

        // -------------------------
        // Approval predictor
        // -------------------------
        $credit_ok = ($credit && $credit->remaining_days >= $days) ? 1 : 0;
        $recent_feat = ($recentCount === 0) ? 0 : $recentCount;
        $doc_present_feat = !empty($data['file_path']) ? 1 : 0;
        $conflict_feat = $conflicts;
        $days_feat = $days;
        $type_priority = strtolower($leaveType->name) === 'medical' ? 2 : 1;

        $weights = [
            'bias' => -1.0,
            'credit_ok' => 1.5,
            'recent_count' => -0.6,
            'doc_present' => 1.0,
            'conflict_count' => -1.2,
            'days' => -0.05,
            'type_priority' => 0.8,
        ];

        $z = $weights['bias']
            + $weights['credit_ok'] * $credit_ok
            + $weights['recent_count'] * $recent_feat
            + $weights['doc_present'] * $doc_present_feat
            + $weights['conflict_count'] * $conflict_feat
            + $weights['days'] * $days_feat
            + $weights['type_priority'] * $type_priority;

        $probability = 1 / (1 + exp(-$z));
        $steps[] = ['text' => "Approval probability (model) calculated.", 'score' => $score, 'type' => 'info'];

        // -------------------------
        // Final status & notes
        // -------------------------
        $finalStatus = null;
        $finalReviewType = $data['review_type'] ?? 'auto';
        $statusNoteParts = [];

        if ($probability >= 0.8 && $score >= 0) {
            $finalStatus = 'approved';
            $statusNoteParts[] = 'Auto-approved';
        } elseif ($probability >= 0.5 || ($score >= 0 && $probability >= 0.45)) {
            $finalStatus = 'pending';
            $finalReviewType = 'manual';
            $statusNoteParts[] = 'Manual review';
        } else {
            $finalStatus = 'rejected';
            $statusNoteParts[] = 'Auto-rejected';
        }

        if (!empty($fraudReasons)) $statusNoteParts[] = "Fraud checks: " . implode('; ', $fraudReasons);

        $data['final_score'] = $score;
        $data['status'] = $finalStatus;
        $data['review_type'] = $finalReviewType;
        $data['status_note'] = implode(' | ', $statusNoteParts);

        if ($data['status'] === 'approved') $steps[] = ['text' => "Final decision: Approved", 'score' => $score, 'type' => 'success'];
        elseif ($data['status'] === 'pending') $steps[] = ['text' => "Final decision: Pending / Manual review", 'score' => $score, 'type' => 'warning'];
        else $steps[] = ['text' => "Final decision: Rejected", 'score' => $score, 'type' => 'error'];

        // -------------------------
        // Persist LeaveRequest
        // -------------------------
        $leave = LeaveRequest::create($data);

        // Deduct credits if approved
        if ($data['status'] === 'approved' && isset($credit) && $credit) {
            $credit->remaining_days = max(0, $credit->remaining_days - $days);
            $credit->save();
        }

        // Send user/admin emails
        if ($data['status'] === 'approved' || $data['status'] === 'rejected') {
            Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
        } elseif ($data['review_type'] === 'manual') {
            Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
            $admin = Admin::first();
            if ($admin) Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
        }

        // Store steps in session and return
        session(['leave_steps' => $steps]);

        return [
            'success' => true,
            'message' => null,
            'leave' => $leave,
            'steps' => $steps,
        ];
    }
}
