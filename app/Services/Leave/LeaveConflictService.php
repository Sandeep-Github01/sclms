<?php

namespace App\Services\Leave;

use App\Models\BlackoutPeriod;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveConflictService
{
    /**
     * $request - incoming Request
     * $validation - output from LeaveValidationService (array)
     *
     * Returns:
     * [
     *   'success' => true|false,
     *   'message' => 'error message if any',
     *   'steps' => [...],
     *   // returned conflict-related computed values:
     *   'dailyDetails' => [...],
     *   'maxAbsentees' => int,
     *   'riskRatio' => float,
     *   'conflicts' => int,
     *   'inBlackout' => bool,
     * ]
     */
    public function checkConflicts($request, $validation)
    {
        $user = Auth::user();
        $steps = $validation['steps'] ?? [];
        $score = $validation['score'] ?? 0;

        $department = $validation['department'];
        $start = $validation['start'];
        $end = $validation['end'];
        $days = $validation['days'];
        $role = $user->role;
        $semester = $user->semester;

        // -------------------------
        // Blackout check (exists)
        // -------------------------
        $deptArr = [(string)$department->id];
        $semArr  = [(string)$semester];

        $inBlackout = BlackoutPeriod::where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('start_date', '<=', $start)->where('end_date', '>=', $end);
                });
        })->where(function ($q) use ($deptArr) {
            $q->whereNull('department_id')->orWhereJsonContains('department_id', $deptArr);
        })->where(function ($q) use ($semArr) {
            $q->whereNull('semester')->orWhereJsonContains('semester', $semArr);
        })->exists();

        if ($inBlackout) {
            $steps[] = ['text' => "Rejected due to blackout period.", 'score' => $score, 'type' => 'error'];
            return [
                'success' => false,
                'message' => 'Falls in blackout period.',
                'steps'   => $steps,
                'inBlackout' => true,
            ];
        }

        $steps[] = ['text' => "No blackout conflict.", 'score' => $score, 'type' => 'success'];

        // -------------------------
        // Department load & conflicts  (👻 GHOST-FIX INSIDE)
        // -------------------------
        $current = $start->copy();
        $dailyDetails = [];
        $maxAbsentees = 0;

        while ($current->lte($end)) {
            $d = $current->toDateString();

            // 🔍 Only count REAL active users
            $dailyCountQuery = LeaveRequest::where('status', 'approved')
                ->where('department_id', $department->id)
                ->whereDate('start_date', '<=', $d)
                ->whereDate('end_date', '>=', $d)
                ->where('role', $role)
                ->whereHas('user', fn($q) => $q->where('status', 'active')); // ✅ active users only

            if ($role === 'student' && $semester) {
                $dailyCountQuery->where('semester', $semester);
            }

            $dailyCount = $dailyCountQuery->count();
            $dailyDetails[$d] = $dailyCount;
            if ($dailyCount > $maxAbsentees)
                $maxAbsentees = $dailyCount;

            $current->addDay();
        }


        $teacher_threshold = config('leave.thresholds.teacher', 1);
        $student_threshold = config('leave.thresholds.student', 3);
        $threshold = $role === 'teacher' ? $teacher_threshold : $student_threshold;
        $riskRatio = $maxAbsentees / max(1, $threshold);

        if ($riskRatio > 1.5) {
            $steps[] = ['text' => "Department critically understaffed (max_absentees={$maxAbsentees})", 'score' => $score, 'type' => 'error'];

            if ($role === 'teacher') {
                $steps[] = ['text' => "Rejected: Department critically understaffed.", 'score' => $score, 'type' => 'error'];
                return [
                    'success' => false,
                    'message' => 'Department critically understaffed.',
                    'steps' => $steps,
                    'dailyDetails' => $dailyDetails,
                    'maxAbsentees' => $maxAbsentees,
                    'riskRatio' => $riskRatio,
                ];
            } else {
                $steps[] = ['text' => "High department load risk (students) → manual review.", 'score' => $score, 'type' => 'warning'];
                return [
                    'success' => true,
                    'message' => null,
                    'steps' => $steps,
                    'dailyDetails' => $dailyDetails,
                    'maxAbsentees' => $maxAbsentees,
                    'riskRatio' => $riskRatio,
                    'force_manual_due_department_load' => true,
                ];
            }
        }

        // -------------------------
        // Conflict checks (approved leaves overlapping)  (👻 GHOST-FIX AGAIN)
        // -------------------------
        $startCopy = $start;
        $endCopy = $end;

        $conflictsQuery = LeaveRequest::where('status', 'approved')
            ->where('department_id', $department->id)
            ->where('role', $role)
            ->where(function ($q) use ($startCopy, $endCopy) {
                $q->whereBetween('start_date', [$startCopy, $endCopy])
                    ->orWhereBetween('end_date', [$startCopy, $endCopy])
                    ->orWhere(function ($q2) use ($startCopy, $endCopy) {
                        $q2->where('start_date', '<=', $startCopy)->where('end_date', '>=', $endCopy);
                    });
            })
            ->whereHas('user', fn($q) => $q->where('status', 'active')); // 👻 ghost filter

        if ($role === 'student') {
            $conflictsQuery->where('semester', $semester);
        }

        $conflicts = $conflictsQuery->count();

        if ($role === 'teacher') {
            if ($conflicts >= 1) {
                $steps[] = ['text' => "Rejected: Another teacher already on leave.", 'score' => $score, 'type' => 'error'];
                return [
                    'success' => false,
                    'message' => 'Departmental conflict: teacher absent.',
                    'steps' => $steps,
                    'conflicts' => $conflicts,
                    'dailyDetails' => $dailyDetails,
                    'maxAbsentees' => $maxAbsentees,
                    'riskRatio' => $riskRatio,
                ];
            } else {
                $steps[] = ['text' => "No teacher conflict.", 'score' => $score, 'type' => 'success'];
            }
        } else {
            if ($conflicts >= 3) {
                $steps[] = ['text' => "Rejected: {$conflicts} peers already on leave.", 'score' => $score, 'type' => 'error'];
                return [
                    'success' => false,
                    'message' => 'Departmental conflict: too many students absent.',
                    'steps' => $steps,
                    'conflicts' => $conflicts,
                    'dailyDetails' => $dailyDetails,
                    'maxAbsentees' => $maxAbsentees,
                    'riskRatio' => $riskRatio,
                ];
            } elseif ($conflicts === 2) {
                $steps[] = ['text' => "2 peers already on leave → warning.", 'score' => $score, 'type' => 'warning'];
            } elseif ($conflicts === 1) {
                $steps[] = ['text' => "1 peer already on leave → warning.", 'score' => $score, 'type' => 'warning'];
            } else {
                $steps[] = ['text' => "No peer conflict.", 'score' => $score, 'type' => 'success'];
            }
        }

        return [
            'success' => true,
            'message' => null,
            'steps' => $steps,
            'dailyDetails' => $dailyDetails,
            'maxAbsentees' => $maxAbsentees,
            'riskRatio' => $riskRatio,
            'conflicts' => $conflicts,
            'inBlackout' => false,
        ];
    }
}
