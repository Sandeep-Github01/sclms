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
    protected PenaltyService $penalty;

    /* avoid duplicate step lines */
    private array $seenLog = [];

    private function addOnce(array &$steps, string $text, int $score = 0, string $type = 'info'): void
    {
        $key = md5($text);
        if (!isset($this->seenLog[$key])) {
            $this->seenLog[$key] = true;
            $steps[] = ['text' => $text, 'score' => $score, 'type' => $type];
        }
    }

    public function __construct(PenaltyService $penalty)
    {
        $this->penalty = $penalty;
    }

    public function createLeave($request, $validation, $conflict, $creditOutput)
    {
        return DB::transaction(function () use ($request, $validation, $conflict, $creditOutput) {

            $user = Auth::user();
            // Use creditOutput steps (which already contain all previous steps)
            $steps = $creditOutput['steps'] ?? [];
            $score = $creditOutput['score'] ?? 0;

            /* ---------- basic data ---------- */
            $leaveType = $validation['leaveType'];
            $department = $validation['department'];
            $start = $validation['start'];
            $end = $validation['end'];
            $days = $validation['days'];
            $filePath = $validation['filePath'] ?? null;
            $fraudReasons = $validation['fraudReasons'] ?? [];
            $isManual = $validation['isManual'] ?? false;

            if (!empty($conflict['force_manual_due_department_load'])) {
                $isManual = true;
            }

            /* ---------- block check ---------- */
            if ($user->leave_blocked_until && Carbon::now()->lt($user->leave_blocked_until)) {
                return ['success' => false, 'message' => 'Leave blocked until ' . $user->leave_blocked_until];
            }

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
                'probability' => null,               // ← filled below
            ];

            /* ---------- manual review route ---------- */
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
                return ['success' => true, 'message' => null, 'leave' => $leave, 'steps' => $steps];
            }

            /* ---------- medical provisional ---------- */
            if (strtolower($leaveType->name) === 'medical' && empty($filePath)) {
                $data['review_type'] = 'manual';
                $data['status'] = 'provisional';
                $data['document_deadline'] = Carbon::now()->addDays(3);

                $leave = LeaveRequest::create($data);
                Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
                foreach (Admin::all() as $admin) {
                    Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
                }
                $this->addOnce($steps, 'Provisional medical leave created.', $score, 'warning');
                session(['leave_steps' => $steps]);
                return ['success' => true, 'message' => null, 'leave' => $leave, 'steps' => $steps];
            }

            /* ---------- logistic regression + decision + closing steps ---------- */
            // 1.  merge raw features from all services
            $features = array_merge(
                $validation['features'] ?? [],
                $conflict['features'] ?? [],
                $creditOutput['features'] ?? []
            );

            // 2.  add final engineered features
            $features['days'] = $days;
            $features['type_priority'] = strtolower($leaveType->name) === 'medical' ? 2 : 1;
            $features['conflict_count'] = $conflict['conflicts'] ?? 0;
            $features['doc_present'] = $creditOutput['doc_present'] ? 1 : 0;
            $features['credit_ok'] = ($creditOutput['credit'] && $creditOutput['credit']->remaining_days >= $days) ? 1 : 0;

            // 3.  load weights & bias from config
            $weights = config('leave.weights');
            $bias = $weights['bias'] ?? -1.0;

            // 4.  predict
            $model = new LogisticRegressionService($weights, $bias);
            $probability = $model->predict($features);

            // 5.  decide BEFORE using $finalStatus
            $cfg = config('leave.decision_thresholds');
            if ($probability >= $cfg['auto_approve'] && $score >= 0) {
                $finalStatus = 'approved';
                $finalReview = 'auto';
            } elseif ($probability >= $cfg['manual_review'] || ($score >= 0 && $probability >= 0.45)) {
                $finalStatus = 'pending';
                $finalReview = 'manual';
            } else {
                $finalStatus = 'rejected';
                $finalReview = 'auto';
            }

            // 6.  store probability in the row we are about to insert
            $data['probability'] = $probability;

            /// Add final steps (using addOnce to prevent duplicates)
            $this->addOnce($steps, 'Approval probability (model): ' . round($probability * 100, 1) . '%', 0, 'info');

            $decisionText = 'Final decision: ' . ucfirst($finalStatus) . ' (' . ucfirst($finalReview) . ')';
            $decisionType = $finalStatus === 'approved' ? 'success' : ($finalStatus === 'rejected' ? 'error' : 'warning');
            $this->addOnce($steps, $decisionText, 0, $decisionType);
            /* ---------- build note & persist ---------- */
            $noteParts = [];
            if ($fraudReasons) {
                $noteParts[] = 'Fraud checks: ' . implode('; ', $fraudReasons);
            }

            $data['final_score'] = $score;
            $data['status'] = $finalStatus;
            $data['review_type'] = $finalReview;
            $data['status_note'] = implode(' | ', $noteParts);

            /* ---------- persist leave ---------- */
            $leave = LeaveRequest::create($data);

            if ($fraudReasons) {
                $leave->fraud_flags = $fraudReasons;
                $leave->save();
                $this->penalty->markAbuse($leave, 'fraud_detected', null, 'system', null);
            }

            /* ---------- credit deduction ---------- */
            if ($finalStatus === 'approved' && $creditOutput['credit']) {
                $credit = $creditOutput['credit'];
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

            /* ---------- mails ---------- */
            if (in_array($finalStatus, ['approved', 'rejected'])) {
                Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
            } elseif ($finalReview === 'manual') {
                Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
                foreach (Admin::all() as $admin) {
                    Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
                }
            }

            session(['leave_steps' => $steps]);
            // 7.  persist evaluation for audit / retraining
            DB::table('leave_evaluations')->insert([
                'leave_request_id' => $leave->id,
                'features' => json_encode($features),
                'probability' => $probability,
                'score' => $score,
                'steps' => json_encode($steps),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => null,
                'leave' => $leave,
                'steps' => $steps,
            ];
        });
    }
}