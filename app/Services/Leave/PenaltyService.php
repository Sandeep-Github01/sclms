<?php

namespace App\Services\Leave;

use App\Models\User;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class PenaltyService
{
    protected $rules;
    protected $thresholds;

    public function __construct()
    {
        $this->rules = config('penalty_rules.rules');
        $this->thresholds = config('penalty_rules.thresholds');
    }

    /**
     * Apply points directly to a user by rule key.
     */
    public function apply(User $user, string $ruleKey, LeaveRequest $leave = null)
    {
        if (!isset($this->rules[$ruleKey])) {
            return [
                'success' => false,
                'message' => "Unknown penalty rule: {$ruleKey}"
            ];
        }

        $points = $this->rules[$ruleKey];

        $user->penalty_points += $points;

        // evaluate thresholds and take actions (block/reduce credits/etc)
        $penaltyAction = $this->evaluateThreshold($user);

        $user->save();

        if ($leave) {
            $leave->notes = trim(($leave->notes ?? '') . "\nPenalty Applied: {$ruleKey} ({$points} pts)");
            $leave->save();
        }

        return [
            'success' => true,
            'points_added' => $points,
            'new_total' => $user->penalty_points,
            'action_taken' => $penaltyAction
        ];
    }

    /**
     * Mark a leave as abused and apply penalty.
     * $flaggedBy = 'system' or 'admin'
     * $flaggedById = admin user id when admin flagged
     */
    public function markAbuse(LeaveRequest $leave, string $reasonCode, int $points = null, string $flaggedBy = 'system', int $flaggedById = null)
    {
        $user = $leave->user;

        // set abuse fields on leave
        $leave->abuse = 1;
        $leave->abuse_reason = $reasonCode;
        $leave->flagged_by = in_array($flaggedBy, ['system', 'admin']) ? $flaggedBy : 'system';
        $leave->flagged_by_id = $flaggedById;
        $leave->status = 'rejected'; // per your choice: rejected_abused can be handled in UI by interpreting abuse=1
        $leave->save();

        // determine points from config if not passed
        $pointsToAdd = $points ?? ($this->rules[$reasonCode] ?? ($this->rules['confirmed_leave_abuse'] ?? 0));

        // apply points to user
        $this->apply($user, $reasonCode, $leave);

        // return result
        return [
            'success' => true,
            'reason' => $reasonCode,
            'points' => $pointsToAdd,
            'user_total' => $user->penalty_points
        ];
    }

    /**
     * Check penalty thresholds and perform actions (block or reduce credits).
     */
    private function evaluateThreshold(User $user)
    {
        $points = $user->penalty_points;

        if ($points >= 20) {
            $user->leave_blocked_until = Carbon::now()->addDays(30);
            $user->save();
            return 'block_non_critical_leaves';
        }

        if ($points >= 15) {
            $this->reduceCredits($user);
            return 'reduce_leave_credit';
        }

        if ($points >= 10) {
            // set a flag in user record if needed for forced manual review
            // we rely on checking penalty_points >= 10 where needed
            return 'force_manual_review';
        }

        if ($points >= 5) {
            return 'warning';
        }

        return null;
    }

    /**
     * Reduce user's leave credits by 20% (applies to all leave types).
     */
    private function reduceCredits(User $user)
    {
        foreach ($user->leaveCredits as $credit) {
            $credit->remaining_days = max(0, intval($credit->remaining_days * 0.8));
            $credit->save();
        }
    }
}
