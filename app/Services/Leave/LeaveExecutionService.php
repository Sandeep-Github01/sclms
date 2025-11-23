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

    /* ---------- duplicate guard ---------- */
    private array $seenLog = [];

    private function addOnce(array &$steps, string $text, int $score = 0, string $type = 'info'): void
    {
        $key = md5($text);
        if (!isset($this->seenLog[$key])) {
            $this->seenLog[$key] = true;
            $steps[] = ['text' => $text, 'score' => $score, 'type' => $type];
        }
    }
    /* ------------------------------------- */

    public function __construct(PenaltyService $penalty)
    {
        $this->penalty = $penalty;
    }

    public function createLeave($request, $validation, $conflict, $creditOutput)
    {
        return DB::transaction(
            function () use ($request, $validation, $conflict, $creditOutput) {
                $user = Auth::user();

                // block check
                if (!empty($user->leave_blocked_until) && Carbon::now()->lt($user->leave_blocked_until)) {
                    return [
                        'success' => false,
                        'message' => 'Your leave privileges are temporarily blocked until ' . $user->leave_blocked_until,
                    ];
                }

                // merge inputs
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

                if (!empty($conflict['force_manual_due_department_load'])) {
                    $isManual = true;
                }

                // base payload
                $data = [
                    'user_id' => $user->id,
                    'type_id' => $leaveType->id,
                    'department_id' => $department->id,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'reason' => $request->reason,
                    'file_path' => $filePath,
                    'status' => 'pending',
                    'review_type' => 'auto',
                    'final_score' => null,
                    'status_note' => null,
                    'role' => $user->role,
                    'semester' => $user->semester,
                    'abuse' => 0,
                    'abuse_reason' => null,
                    'flagged_by' => null,
                    'flagged_by_id' => null,
                    'fraud_flags' => null,
                    'document_status' => $filePath ? 'submitted' : 'pending',
                    'document_deadline' => null,
                ];

                /* ---------- MANUAL REVIEW (fraud or dept load) ---------- */
                if ($isManual) {
                    $data['review_type'] = 'manual';
                    $leave = LeaveRequest::create($data);

                    if ($fraudReasons) {
                        $leave->fraud_flags = $fraudReasons;
                        $leave->save();
                    }

                    Mail::to($user->email)->send(new LeaveSubmittedMail($leave));

                    foreach (Admin::all() as $admin) {
                        Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
                    }

                    $this->addOnce($steps, 'Leave submitted for manual review.', $score, 'success');

                    return [
                        'success' => true,
                        'message' => null,
                        'leave' => $leave,
                        'steps' => $steps,
                    ];
                }

                /* ---------- MEDICAL PROVISIONAL ---------- */
                if (strtolower($leaveType->name) === 'medical' && empty($filePath)) {
                    $data['review_type'] = 'manual';
                    $data['status'] = 'provisional';
                    $data['document_deadline'] = Carbon::now()->addDays(3);

                    $leave = LeaveRequest::create($data);

                    Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
                    foreach (Admin::all() as $admin) {
                        Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
                    }

                    $this->addOnce($steps, 'Provisional medical leave created. Document deadline set to ' . $data['document_deadline']->toDateTimeString(), $score, 'warning');

                    session(['leave_steps' => $steps]);

                    return [
                        'success' => true,
                        'message' => null,
                        'leave' => $leave,
                        'steps' => $steps,
                    ];
                }

                /* ---------- AUTO EVALUATION ---------- */
                $credit = $creditOutput['credit'] ?? null;
                $recentCount = $creditOutput['recentCount'] ?? 0;
                $doc_present = $creditOutput['doc_present'] ?? (!empty($filePath) ? 1 : 0);

                $credit_ok = ($credit && $credit->remaining_days >= $days) ? 1 : 0;
                $recent_feat = ($recentCount === 0) ? 0 : $recentCount;
                $doc_feat = !empty($data['file_path']) ? 1 : 0;
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
                    + $weights['doc_present'] * $doc_feat
                    + $weights['conflict_count'] * $conflict_feat
                    + $weights['days'] * $days_feat
                    + $weights['type_priority'] * $type_priority;

                $probability = 1 / (1 + exp(-$z));
                $this->addOnce($steps, 'Approval probability (model) calculated.', $score, 'info');

                /* ---------- FINAL STATUS ---------- */
                $finalStatus = null;
                $finalReview = $data['review_type'];
                $noteParts = [];

                if ($probability >= 0.8 && $score >= 0) {
                    $finalStatus = 'approved';
                    $noteParts[] = 'Auto-approved';
                } elseif ($probability >= 0.5 || ($score >= 0 && $probability >= 0.45)) {
                    $finalStatus = 'pending';
                    $finalReview = 'manual';
                    $noteParts[] = 'Manual review';
                } else {
                    $finalStatus = 'rejected';
                    $noteParts[] = 'Auto-rejected';
                }

                if ($fraudReasons) {
                    $noteParts[] = 'Fraud checks: ' . implode('; ', $fraudReasons);
                }

                $data['final_score'] = $score;
                $data['status'] = $finalStatus;
                $data['review_type'] = $finalReview;
                $data['status_note'] = implode(' | ', $noteParts);

                if ($finalStatus === 'approved') {
                    $this->addOnce($steps, 'Final decision: Approved', $score, 'success');
                } elseif ($finalStatus === 'pending') {
                    $this->addOnce($steps, 'Final decision: Pending / Manual review', $score, 'warning');
                } else {
                    $this->addOnce($steps, 'Final decision: Rejected', $score, 'error');
                }

                /* ---------- PERSIST ---------- */
                $leave = LeaveRequest::create($data);

                if ($fraudReasons) {
                    $leave->fraud_flags = $fraudReasons;
                    $leave->save();
                    $this->penalty->markAbuse($leave, 'fraud_detected', null, 'system', null);
                }

                /* ---------- ATOMIC CREDIT DEDUCTION ---------- */
                if ($finalStatus === 'approved' && $credit) {
                    $affected = DB::table('leave_credits')
                        ->where('id', $credit->id)
                        ->where('remaining_days', '>=', $days)
                        ->decrement('remaining_days', $days);

                    if ($affected === 0) {
                        $leave->update([
                            'status' => 'rejected',
                            'status_note' => 'Credit exhausted by another request.',
                        ]);
                        $this->addOnce($steps, 'Credit gone – leave flipped to rejected.', $score, 'error');
                    }
                }

                if ($finalStatus === 'rejected') {
                    $this->penalty->markAbuse($leave, 'leave_rejected', null, 'system', null);
                }

                /* ---------- MAILS ---------- */
                if (in_array($finalStatus, ['approved', 'rejected'])) {
                    Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
                } elseif ($finalReview === 'manual') {
                    Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
                    foreach (Admin::all() as $admin) {
                        Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
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