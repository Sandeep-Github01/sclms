<?php

namespace App\Services\Leave;

use App\Models\Department;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\BlackoutPeriod;
use App\Models\LeaveCredit;
use App\Models\UserFraudHistory;   
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LeaveValidationService
{
    /**
     * Validates leave request and performs fraud detection algorithm
     *
     * Algorithm Complexity: O(n) where n = user's historical leave count
     * Detects patterns: weekend bridging, emergency abuse, duration outliers,
     * and frequency anomalies. Implements composite scoring for fraud assessment.
     *
     * @param Request $request
     * @return array Validation result with steps, score, and fraud analysis
     */
    public function validateRequest($request)
    {
        $user = Auth::user();
        $steps = [];
        $score = 0;

        // 1. Department existence
        if (!$user->dept_name) {
            return [
                'success' => false,
                'message' => 'You must be assigned to a department.'
            ];
        }

        $steps[] = ['text' => "Department assigned: {$user->dept_name}", 'score' => $score, 'type' => 'success'];

        $department = Department::where('name', $user->dept_name)->first();
        if (!$department) {
            return [
                'success' => false,
                'message' => "Your department '{$user->dept_name}' was not found."
            ];
        }

        $steps[] = ['text' => "Department exists in system.", 'score' => $score, 'type' => 'success'];

        // 2. Validate Inputs
        $request->validate([
            'type_id'    => 'required|integer|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string',
            'document'   => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
        ]);
        $steps[] = ['text' => "Form data validated.", 'score' => $score, 'type' => 'success'];

        // 3. Load leave type
        $leaveType = LeaveType::findOrFail($request->type_id);
        $steps[]  = ['text' => "Leave type: {$leaveType->name}", 'score' => $score, 'type' => 'success'];

        // 4. Calculate duration
        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);
        $days  = $start->diffInDays($end) + 1;

        $steps[] = ['text' => "Duration: {$days} day(s)", 'score' => $score, 'type' => 'success'];

        // 5. Multi-pattern fraud detection algorithm
        $fraudScore = 0;
        $fraudReasons = [];

        // Pattern 1: 4-day weekend bridge detection
        $startWeekday = strtolower($start->format('l'));
        $endWeekday = strtolower($end->format('l'));

        if (($startWeekday === 'thursday' && $endWeekday === 'friday') ||
            ($startWeekday === 'monday' && $endWeekday === 'tuesday')
        ) {
            $fraudScore += 2;
            $fraudReasons[] = "4-day weekend bridge detected ({$startWeekday} to {$endWeekday}).";
        }

        // Pattern 2: Emergency leave abuse (multiple in 60 days)
        $emergencyCount = LeaveRequest::where('user_id', $user->id)
            ->whereHas('leaveType', fn($q) => $q->whereRaw("LOWER(name)='emergency'"))
            ->whereBetween('start_date', [
                Carbon::now()->subDays(60)->toDateString(),
                Carbon::now()->toDateString()
            ])
            ->count();

        if ($emergencyCount >= 2) {
            $fraudScore += 3;
            $fraudReasons[] = "Multiple emergency leaves ({$emergencyCount}) in last 60 days.";
        }

        // Pattern 3: Duration statistical outlier (>2x personal average)
        $avgDuration = (float) LeaveRequest::where('user_id', $user->id)
            ->select(DB::raw('AVG(DATEDIFF(end_date, start_date)+1) as avg_days'))
            ->value('avg_days');

        if ($avgDuration && $days > ($avgDuration * 2)) {
            $fraudScore += 2;
            $fraudReasons[] = "Duration unusually long ({$days} days vs average " . round($avgDuration, 2) . ").";
        }

        // Pattern 4: High frequency pattern (5+ leaves in 30 days)
        $freqCount30 = LeaveRequest::where('user_id', $user->id)
            ->whereBetween('start_date', [
                Carbon::now()->subDays(30)->toDateString(),
                Carbon::now()->toDateString()
            ])
            ->count();

        if ($freqCount30 >= 5) {
            $fraudScore += 2;
            $fraudReasons[] = "High leave frequency ({$freqCount30}) in last 30 days.";
        }

        // 🔥 NEW: add old red stars from teacher's notebook (last 30 days)
        $residual = $this->getResidualFraudScore($user->id);
        $fraudScore += $residual;

        if ($residual > 0) {
            $fraudReasons[] = "Carry-over fraud score from past 30 days: {$residual}";
        }

        // Composite scoring: Manual review if fraud score >= 4
        $isManual = $fraudScore >= 4;
        if ($isManual) {
            $steps[] = [
                'text' => "Potential fraud detected: " . implode('; ', $fraudReasons),
                'score' => $score,
                'type' => 'warning'
            ];
        }

        // 6. Secure document upload (Private storage)
        $filePath = null;
        if ($request->hasFile('document')) {
            $filePath = $request->file('document')->store('private/leave_docs', 'local');
            $steps[] = ['text' => "Document uploaded securely.", 'score' => $score, 'type' => 'document'];
        } else {
            $steps[] = ['text' => "No document uploaded.", 'score' => $score, 'type' => 'document'];
        }

        // 🔥 Save today's fraud score into the notebook so tomorrow's kid can't erase it
        UserFraudHistory::create([
            'user_id' => $user->id,
            'score' => $fraudScore,
            'reasons' => $fraudReasons,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        return [
            'success' => true,
            'steps' => $steps,
            'score' => $score,
            'leaveType' => $leaveType,
            'department' => $department,
            'start' => $start,
            'end' => $end,
            'days' => $days,
            'filePath' => $filePath,
            'fraudScore' => $fraudScore,
            'fraudReasons' => $fraudReasons,
            'isManual' => $isManual,
        ];
    }

    /**
     * Sum of fraud scores from the last 30 days that haven't expired
     */
    private function getResidualFraudScore(int $userId): int
    {
        return (int) DB::table('user_fraud_histories')
            ->where('user_id', $userId)
            ->where('expires_at', '>', Carbon::now())
            ->sum('score');
    }
}
