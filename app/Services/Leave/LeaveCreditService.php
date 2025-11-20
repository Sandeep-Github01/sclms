<?php

namespace App\Services\Leave;

use App\Models\LeaveCredit;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveCreditService
{
    /**
     * $request - incoming Request
     * $validation - output from LeaveValidationService (array)
     * $conflict - output from LeaveConflictService (array)
     *
     * Returns:
     * [
     *   'success' => true|false,
     *   'message' => null|'error message',
     *   'steps' => [...],
     *   'score' => int,
     *   'credit' => LeaveCredit|null,
     *   'recentCount' => int,
     *   'doc_present' => bool
     * ]
     */
    public function checkCredits($request, $validation, $conflict)
    {
        $user = Auth::user();
        $steps = $validation['steps'] ?? [];
        $score = $validation['score'] ?? 0;

        $leaveType = $validation['leaveType'];
        $days = $validation['days'];
        $filePath = $validation['filePath'] ?? null;

        // -------------------------
        // Check leave credits
        // -------------------------
        $credit = LeaveCredit::where('user_id', $user->id)
            ->where('type_id', $leaveType->id)
            ->first();

        if ($credit && $credit->remaining_days >= $days) {
            $score += 2;
            $steps[] = ['text' => "Sufficient leave credits ({$credit->remaining_days}).", 'score' => $score, 'type' => 'success'];
        } else {
            $score -= 2;
            $steps[] = ['text' => "Insufficient leave credits or record missing.", 'score' => $score, 'type' => 'error'];
        }

        // -------------------------
        // Recent approved leaves in past 10 days
        // -------------------------
        $recentCount = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereBetween('start_date', [
                Carbon::now()->subDays(10)->toDateString(),
                Carbon::now()->toDateString()
            ])
            ->count();

        $score += ($recentCount === 0) ? 2 : -1;
        $steps[] = ['text' => ($recentCount === 0 ? "No recent leaves." : "{$recentCount} recent leave(s) in past 10 days."), 'score' => $score, 'type' => ($recentCount === 0 ? 'success' : 'warning')];

        // -------------------------
        // Medical doc check / doc scoring
        // -------------------------
        $doc_present = false;
        if (strtolower($leaveType->name) === 'medical') {
            $score += 1;
            if ($filePath) {
                $score += 3;
                $doc_present = true;
                $steps[] = ['text' => "Medical document attached.", 'score' => $score, 'type' => 'success'];
            } elseif ($leaveType->requires_documentation) {
                // reject immediately (as per original logic)
                $data = [
                    'status' => 'rejected',
                    'status_note' => "Medical document required."
                ];
                $steps[] = ['text' => "Rejected: Missing medical document.", 'score' => $score, 'type' => 'error'];
                return [
                    'success' => false,
                    'message' => 'Medical document required.',
                    'steps' => $steps,
                ];
            }
        }

        // Return final credit evaluation
        return [
            'success' => true,
            'message' => null,
            'steps' => $steps,
            'score' => $score,
            'credit' => $credit ?? null,
            'recentCount' => $recentCount,
            'doc_present' => $doc_present,
        ];
    }

    /**
     * Deduct credits after approval (called by execution service).
     * Keeps exact logic: max(0, remaining_days - $days)
     */
    public function deductCredits($credit, $days)
    {
        if (! $credit) return;
        $credit->remaining_days = max(0, $credit->remaining_days - $days);
        $credit->save();
    }
}
