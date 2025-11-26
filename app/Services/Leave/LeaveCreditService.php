<?php
namespace App\Services\Leave;

use App\Models\LeaveCredit;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveCreditService
{
    public function checkCredits($request, $validation, $conflict)
    {
        $user = Auth::user();
        $steps = $conflict['steps'] ?? [];
        $score = $conflict['score'] ?? 0;

        $leaveType = $validation['leaveType'];
        $days = $validation['days'];
        $filePath = $validation['filePath'] ?? null;

        $features = [
            'credit_ok' => 0,
            'credit_pct' => 0,
            'recent_count' => 0,
            'doc_present' => 0,
        ];

        // credit check
        $credit = LeaveCredit::where('user_id', $user->id)
            ->where('type_id', $leaveType->id)
            ->first();
        $creditScore = 0;
        if ($credit) {
            $pct = $credit->remaining_days / max(1, $leaveType->max_days);
            $features['credit_pct'] = $pct;
            $features['credit_ok'] = $credit->remaining_days >= $days ? 1 : 0;

            if ($credit->remaining_days >= $days) {
                $creditScore = 2 + (int) ($pct * 3);
                $steps[] = ['text' => "Sufficient credits ({$credit->remaining_days}/{$leaveType->max_days})", 'score' => $creditScore, 'type' => 'success'];
            } else {
                $creditScore = -3;
                $steps[] = ['text' => "Insufficient credits", 'score' => $creditScore, 'type' => 'error'];
            }
        } else {
            $creditScore = -2;
            $steps[] = ['text' => "No credit record", 'score' => $creditScore, 'type' => 'error'];
        }
        $score += $creditScore;

        // NEW: recent leaves count
        $recent = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereBetween('start_date', [now()->subDays(30), now()])
            ->count();
        $features['recent_count'] = $recent;
        $recentScore = $recent === 0 ? 3 : -$recent;
        $steps[] = ['text' => $recent === 0 ? 'No recent leaves' : "$recent recent leave(s)", 'score' => $recentScore, 'type' => $recent > 2 ? 'warning' : 'info'];
        $score += $recentScore;

        // NEW: medical-document step
        $docScore = 0;
        if (strtolower($leaveType->name) === 'medical') {
            if ($filePath) {
                $docScore = 4;
                $features['doc_present'] = 1;
                $steps[] = ['text' => 'Medical document attached', 'score' => $docScore, 'type' => 'success'];
            } else {
                $steps[] = ['text' => 'No document uploaded', 'score' => 0, 'type' => 'info'];
            }
            $score += $docScore;
        }

        return [
            'success' => true,
            'message' => null,
            'steps' => $steps,
            'score' => $score,
            'credit' => $credit,
            'recentCount' => $recent,
            'doc_present' => (bool) $filePath,
            'features' => $features,
        ];
    }

    public function deductCredits($credit, $days)
    {
        if (!$credit)
            return;
        $credit->remaining_days = max(0, $credit->remaining_days - $days);
        $credit->save();
    }
}