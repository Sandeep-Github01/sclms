<?php
namespace App\Services\Leave;

use App\Models\BlackoutPeriod;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveConflictService
{
    public function checkConflicts($request, $validation)
    {
        $user = Auth::user();

        // RECEIVE steps and score from validation
        $steps = $validation['steps'] ?? [];
        $score = $validation['score'] ?? 0;

        $dept = $validation['department'];
        $start = $validation['start'];
        $end = $validation['end'];
        $role = $user->role;
        $semester = $user->semester;

        $features = [
            'blackout' => 0,
            'max_absentees' => 0,
            'peer_conflicts' => 0,
        ];

        // blackout
        $inBlack = BlackoutPeriod::where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhereRaw('? between start_date and end_date', [$start]);
        })->where(function ($q) use ($dept) {
            $q->whereNull('department_id')
                ->orWhereJsonContains('department_id', [(string) $dept->id]);
        })->where(function ($q) use ($semester) {
            $q->whereNull('semester')
                ->orWhereJsonContains('semester', [(string) $semester]);
        })->exists();

        if ($inBlack) {
            $score -= 10;
            $features['blackout'] = 1;
            $steps[] = ['text' => 'Blackout period conflict', 'score' => -10, 'type' => 'error'];
            return ['success' => false, 'message' => 'Falls in blackout', 'steps' => $steps, 'inBlackout' => true, 'score' => $score, 'features' => $features];
        }
        $steps[] = ['text' => 'No blackout conflict', 'score' => 1, 'type' => 'success'];
        $score += 1; 

        // peer-conflict step
        $current = $start->copy();
        $maxAbs = 0;
        while ($current->lte($end)) {
            $cnt = LeaveRequest::where('status', 'approved')
                ->where('department_id', $dept->id)
                ->whereDate('start_date', '<=', $current)
                ->whereDate('end_date', '>=', $current)
                ->where('role', $role)
                ->whereHas('user', fn($q) => $q->where('status', 'active'))
                ->when($role === 'student', fn($q) => $q->where('semester', $semester))
                ->count();
            if ($cnt > $maxAbs)
                $maxAbs = $cnt;
            $current->addDay();
        }
        $features['max_absentees'] = $maxAbs;

        $peerScore = $this->peerScore($role, $maxAbs);
        $score += $peerScore['score'];
        $steps[] = ['text' => $peerScore['msg'], 'score' => $peerScore['score'], 'type' => $peerScore['type']];

        return [
            'success' => true,
            'message' => null,
            'steps' => $steps,
            'score' => $score,
            'maxAbsentees' => $maxAbs,
            'riskRatio' => $maxAbs / max(1, $role === 'teacher' ? 1 : 3),
            'conflicts' => $maxAbs,
            'inBlackout' => false,
            'features' => $features,
        ];
    }

    private function peerScore($role, $abs)
    {
        if ($role === 'teacher') {
            return $abs >= 1
                ? ['score' => -5, 'msg' => 'Teacher conflict', 'type' => 'error']
                : ['score' => 2, 'msg' => 'No teacher conflict', 'type' => 'success'];
        }
        return match ($abs) {
            0 => ['score' => 3, 'msg' => 'No peer conflict', 'type' => 'success'],
            1 => ['score' => 1, 'msg' => '1 peer on leave', 'type' => 'info'],
            2 => ['score' => -1, 'msg' => '2 peers on leave', 'type' => 'warning'],
            3 => ['score' => -5, 'msg' => '3 peers on leave', 'type' => 'error'],
            default => ['score' => -10, 'msg' => 'Too many peers absent', 'type' => 'error'],
        };
    }
}