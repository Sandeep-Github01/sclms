<?php

namespace App\Services\Leave;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Admin;
use App\Models\LeaveRequest;
use App\Mail\LeaveSubmittedMail;
use App\Mail\LeaveManualReviewMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LeaveExecutionService
{
    protected $penalty;

    public function __construct(PenaltyService $penalty)
    {
        $this->penalty = $penalty;
    }

    public function createLeave($request, $validation, $conflict, $creditOutput)
    {
        return DB::transaction(
            function () use ($request, $validation, $conflict, $creditOutput) {
                $user = Auth::user();

                // Block check (if user is blocked due to penalties)
                if (!empty($user->leave_blocked_until) && Carbon::now()->lt($user->leave_blocked_until)) {
                    return [
                        'success' => false,
                        'message' => 'Your leave privileges are temporarily blocked until ' . $user->leave_blocked_until,
                    ];
                }

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

                // Build base data array
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
                    'abuse' => 0,
                    'abuse_reason' => null,
                    'flagged_by' => null,
                    'flagged_by_id' => null,
                    'fraud_flags' => null,
                    'document_status' => $filePath ? 'submitted' : 'pending',
                    'document_deadline' => null,
                ];

                // If earlier fraud forced manual
                if ($isManual) {
                    $data['review_type'] = 'manual';
                    $leave = LeaveRequest::create($data);

                    if (!empty($fraudReasons)) {
                        $leave->fraud_flags = $fraudReasons;
                        $leave->save();
                    }

                    Mail::to($user->email)->send(new LeaveSubmittedMail($leave));

                    $admins = Admin::all();
                    if ($admins->isNotEmpty()) {
                        foreach ($admins as $admin) {
                            Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
                        }
                    }

                    $steps[] = ['text' => "Leave submitted for manual review.", 'score' => $score, 'type' => 'success'];

                    return [
                        'success' => true,
                        'message' => null,
                        'leave' => $leave,
                        'steps' => $steps,
                    ];
                }

                // Medical provisional leave
                if (strtolower($leaveType->name) === 'medical' && empty($filePath)) {
                    $data['review_type'] = 'manual';
                    $data['status'] = 'provisional';
                    $data['document_status'] = 'pending';
                    $data['document_deadline'] = Carbon::now()->addDays(3);
                    $leave = LeaveRequest::create($data);

                    Mail::to($user->email)->send(new LeaveSubmittedMail($leave));

                    $admins = Admin::all();
                    if ($admins->isNotEmpty()) {
                        foreach ($admins as $admin) {
                            Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
                        }
                    }

                    $steps[] = ['text' => "Provisional medical leave created. Document deadline set to " . $data['document_deadline']->toDateTimeString(), 'score' => $score, 'type' => 'warning'];
                    session(['leave_steps' => $steps]);

                    return [
                        'success' => true,
                        'message' => null,
                        'leave' => $leave,
                        'steps' => $steps,
                    ];
                }

                // Auto evaluation
                $credit = $creditOutput['credit'] ?? null;
                $recentCount = $creditOutput['recentCount'] ?? 0;
                $doc_present = $creditOutput['doc_present'] ?? (!empty($filePath) ? 1 : 0);

                $credit_ok = ($credit && $credit->remaining_days >= $days) ? 1 : 0;
                $recent_feat = ($recentCount === 0) ? 0 : $recentCount;
                $doc_present_feat = !empty($data['file_path']) ? 1 : 0;
                $conflict_feat = $conflict['conflicts'] ?? 0;
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

                // Final status logic
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

                // Persist LeaveRequest
                $leave = LeaveRequest::create($data);

                if (!empty($fraudReasons)) {
                    $leave->fraud_flags = $fraudReasons;
                    $leave->save();
                    $this->penalty->markAbuse($leave, 'fraud_detected', null, 'system', null);
                }

                // 🔒 RACE-FIX: atomic candy grab – only one hand at a time!
                if ($data['status'] === 'approved' && $credit) {
                    $affected = DB::table('leave_credits')
                        ->where('id', $credit->id)
                        ->where('remaining_days', '>=', $days)
                        ->decrement('remaining_days', $days);

                    if ($affected === 0) {
                        // oops – someone else ate the candies first
                        $leave->update(['status' => 'rejected', 'status_note' => 'Credit exhausted by another request.']);
                        $steps[] = ['text' => 'Credit gone – leave flipped to rejected.', 'score' => $score, 'type' => 'error'];
                    }
                }

                if ($data['status'] === 'rejected') {
                    $this->penalty->markAbuse($leave, 'leave_rejected', null, 'system', null);
                }

                // Mails
                if (in_array($data['status'], ['approved', 'rejected'])) {
                    Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
                } elseif ($data['review_type'] === 'manual') {
                    Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
                    $admins = Admin::all();
                    if ($admins->isNotEmpty()) {
                        foreach ($admins as $admin) {
                            Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
                        }
                    }
                }

                session(['leave_steps' => $steps]);

                return [
                    'success' => true,
                    'message' => null,
                    'leave' => $leave,
                    'steps' => $steps,
                ];
            }
        );
    }
}
