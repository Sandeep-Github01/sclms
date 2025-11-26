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
use Illuminate\Http\Request;

class LeaveValidationService
{
    public function validateRequest($request)
    {
        $user = Auth::user();
        $steps = [];
        $score = 0;
        $appTime = Carbon::now();
        $isManual = false;

        // --- basic checks (no score) ---
        if (!$user->dept_name) {
            return ['success' => false, 'message' => 'You must be assigned to a department.'];
        }
        $department = Department::where('name', $user->dept_name)->first();
        if (!$department) {
            return ['success' => false, 'message' => "Department {$user->dept_name} not found."];
        }

        $steps[] = ['text' => "Department assigned: {$user->dept_name}", 'score' => null, 'type' => 'info'];
        $steps[] = ['text' => "Department exists in system.", 'score' => null, 'type' => 'success'];

        $request->validate([
            'type_id' => 'required|integer|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'document' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
        ]);
        $steps[] = ['text' => "Form data validated.", 'score' => null, 'type' => 'success'];

        $leaveType = LeaveType::findOrFail($request->type_id);
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $days = $start->diffInDays($end) + 1;

        // --- feature collection ---
        $features = [
            'late_night' => 0,
            'last_minute' => 0,
            'weekend_bridge' => 0,
            'early_application' => 0,
            'fraud_score' => 0,
        ];

        // NEW: late-night submission
        $hour = $appTime->hour;
        if ($hour >= 22 || $hour <= 6) {
            $score -= 2;
            $features['late_night'] = 1;
            $steps[] = ['text' => "Late-night application ({$hour}:00)", 'score' => -2, 'type' => 'warning'];
        }

        // NEW: last-minute request
        $hrs = $start->diffInHours($appTime);
        if ($hrs < 24) {
            $score -= 3;
            $features['last_minute'] = 1;
            $steps[] = ['text' => "Last-minute request (" . round($hrs, 2) . "h notice)", 'score' => -3, 'type' => 'error'];
        }

        // NEW: weekend bridge (Original logic kept, as it looks for three-day blocks, but is less relevant for a Sunday-only holiday)
        $bridge = $this->weekendBridge($start, $end);
        if ($bridge < 0) {
            $score += $bridge;
            $features['weekend_bridge'] = 1;
            $steps[] = ['text' => 'Standard weekend bridge detected', 'score' => $bridge, 'type' => 'warning'];
        }

        // NEW: early-bird bonus
        $ahead = $appTime->diffInDays($start);
        if ($ahead >= 7) {
            $bonus = min(3, intdiv($ahead, 7));
            $score += $bonus;
            $features['early_application'] = 1;
            $steps[] = ['text' => "Early application ({$ahead}d ahead)", 'score' => $bonus, 'type' => 'success'];
        }

        // leave-type & duration
        $typeScore = $this->typeScore($leaveType->name, $days);
        $durScore = $this->durationScore($leaveType->name, $days);
        $score += $typeScore + $durScore;
        $steps[] = ['text' => "Leave type: {$leaveType->name}", 'score' => $typeScore, 'type' => 'info'];
        $steps[] = ['text' => "Duration: {$days} day(s)", 'score' => $durScore, 'type' => 'info'];

        // document
        $docPath = null;
        if ($request->hasFile('document')) {
            $docPath = $request->file('document')->store('leave_docs', 'local');
            // Score for document upload is handled in LeaveCreditService for Medical leaves,
            // but we keep the step here for general record.
            $steps[] = ['text' => "Document uploaded", 'score' => 0, 'type' => 'success'];
        } else {
            $steps[] = ['text' => "No document uploaded", 'score' => 0, 'type' => 'info'];
        }

        // fraud patterns
        $fraud = $this->fraudPatterns($user, $start, $end, $days, $request->reason ?? '');
        $score += $fraud['score'];
        $features['fraud_score'] = $fraud['points'];
        if ($fraud['points'] >= 4)
            $isManual = true;

        // persist fraud history
        UserFraudHistory::create([
            'user_id' => $user->id,
            'score' => $fraud['points'],
            'reasons' => $fraud['reasons'],
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
            'filePath' => $docPath,
            'fraudScore' => $fraud['points'],
            'fraudReasons' => $fraud['reasons'],
            'isManual' => $isManual ?? false,
            'features' => $features,
        ];
    }

    /* ---------- helpers ---------- */

    /**
     * @brief Looks for extended weekend patterns (Sat/Sun). Less relevant for Sunday-only holiday.
     */
    private function weekendBridge(Carbon $start, Carbon $end): int
    {
        // Carbon::isDayOfWeek() is 0=Sunday, 6=Saturday.
        // This is the old logic assuming a standard weekend structure (Fri-Sat or Sat-Sun).
        $map = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
        $s = strtolower($start->format('D'));
        $e = strtolower($end->format('D'));

        $badPairs = [
            'fri sat',
            'fri sat sun',
            'sat sun',
            'sat sun mon',
            'sun mon',
        ];

        return in_array("{$s} {$e}", $badPairs) ? -3 : 0;
    }

    private function typeScore($name, $days)
    {
        return match (strtolower($name)) {
            'medical' => 3,
            'emergency' => $days <= 2 ? 2 : -1,
            'academic' => 2,
            'casual' => $days === 1 ? 1 : -1,
            default => 0
        };
    }

    private function durationScore($name, $days)
    {
        return match (strtolower($name)) {
            'casual' => $days === 1 ? 1 : ($days > 3 ? -3 : -1),
            'emergency' => $days <= 2 ? 1 : ($days > 5 ? -4 : -2),
            'medical' => $days <= 7 ? 0 : ($days > 14 ? -3 : -1),
            'academic' => $days <= 10 ? 0 : -2,
            default => 0
        };
    }

    /**
     * @brief Detects high-risk patterns, specifically updated for a Sunday-only holiday schedule.
     */
    private function fraudPatterns($user, $start, $end, $days, $reason)
    {
        $pts = 0;
        $r = [];

        // --- NEW: Strategic Leave check for Sunday Holiday (Nepal College Schedule) ---
        // Sunday is the non-working day. Look for leaves connecting directly to it.

        $isStrategic = false;

        // 1. One-day leave on Friday (Fri + Sat(W) + Sun(H)) - creates a 3-day break
        if ($days === 1 && $start->isFriday()) {
            $isStrategic = true;
            $r[] = 'Strategic 3-day weekend (Fri)';
        }

        // 2. One-day leave on Monday (Sun(H) + Mon) - extends the break
        if ($days === 1 && $start->isMonday()) {
            $isStrategic = true;
            $r[] = 'Strategic 2-day weekend (Mon)';
        }

        // 3. Two-day leave on Friday-Saturday (Fri + Sat + Sun) - guarantees 3-day break
        if ($days === 2 && $start->isFriday() && $end->isSaturday()) {
            $isStrategic = true;
            $r[] = 'Strategic 3-day weekend (Fri-Sat)';
        }

        // Apply penalty if strategic
        if ($isStrategic) {
            $pts += 3; // Increased penalty for strategic scheduling
        }
        // --- END NEW STRATEGIC CHECK ---

        // emergency abuse
        $em = LeaveRequest::where('user_id', $user->id)
            ->whereHas('leaveType', fn($q) => $q->whereRaw("lower(name)='emergency'"))
            ->whereBetween('start_date', [now()->subDays(60), now()])
            ->count();
        if ($em >= 2) {
            $pts += 3;
            $r[] = "Multiple emergency ($em)";
        }
        // duration outlier
        $avg = LeaveRequest::where('user_id', $user->id)
            ->selectRaw('avg(datediff(end_date,start_date)+1) as avg')->value('avg');
        if ($avg && $days > $avg * 2) {
            $pts += 2;
            $r[] = 'Long duration';
        }
        // high freq
        $freq = LeaveRequest::where('user_id', $user->id)
            ->whereBetween('start_date', [now()->subDays(30), now()])
            ->count();
        if ($freq >= 5) {
            $pts += 2;
            $r[] = 'High freq';
        }
        // residual
        $res = (int) DB::table('user_fraud_histories')
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->sum('score');
        if ($res) {
            $pts += $res;
            $r[] = "Residual $res";
        }
        return ['points' => $pts, 'reasons' => $r, 'score' => $pts * (-1)];
    }
}