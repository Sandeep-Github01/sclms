<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveCredit;
use App\Models\BlackoutPeriod;

class LeaveController extends Controller
{
    // Show leave apply form
    public function create()
    {
        // Sab leave types fetch gara: user dropdown maa dekhaune
        $leaveTypes = LeaveType::all();
        return view('frontend.leave.apply', compact('leaveTypes'));
    }

    // Store new leave request with smart logic
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate inputs
        $request->validate([
            'type_id' => 'required|integer|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'document' => 'nullable|file|max:2048', // max 2MB
        ]);

        // Fetch leave type
        $leaveType = LeaveType::findOrFail($request->type_id);

        // Calculate duration in days
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $days = $start->diffInDays($end) + 1;

        // Prepare base data for LeaveRequest
        $data = [
            'user_id' => $user->id,
            'type_id' => $leaveType->id,
            'department_id' => $user->department_id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'reason' => $request->reason,
            'file_path' => null,
            'status' => 'pending',
            'review_type' => 'auto',
            'final_score' => null,
            'status_note' => null,
        ];

        // Handle file upload if provided
        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('leave_docs', 'public');
            $data['file_path'] = $path;
        }

        // Decide manual vs auto logic
        $isManual = false;
        $notes = [];

        // Case 1: Emergency-type or long-term: manual review
        // Assume leaveType has a field 'name' or some flag for Emergency
        if (strtolower($leaveType->name) === 'emergency') {
            $isManual = true;
            $notes[] = 'Emergency leave, manual review required.';
        }
        // Or if duration exceeds configured max_days (long-term)
        elseif (isset($leaveType->max_days) && $days > $leaveType->max_days) {
            $isManual = true;
            $notes[] = "Duration {$days} days exceeds max allowed {$leaveType->max_days}, manual review.";
        }

        if ($isManual) {
            // Manual: set review_type and leaveRequest
            $data['review_type'] = 'manual';
            $data['status'] = 'pending';
            $data['status_note'] = implode(' ', $notes);

            $leave = LeaveRequest::create($data);

            // TODO: Notify HOD/admin via email that new manual request aayo
            // e.g., Mail::to($adminEmail)->send(...)

            return redirect()->route('leave.result', $leave->id)
                             ->with('info', 'Leave request submitted for manual review.');
        }

        // Else: short-term, auto logic with scoring

        $score = 0;

        // 1. Leave credits check
        $credit = LeaveCredit::where('user_id', $user->id)
                              ->where('type_id', $leaveType->id)
                              ->first();
        if ($credit) {
            if ($credit->remaining_days >= $days) {
                $score += 2;
            } else {
                $score -= 2;
                $notes[] = 'Insufficient leave credits.';
            }
        } else {
            // No credit record: treat as zero
            $score -= 2;
            $notes[] = 'No leave credit record found.';
        }

        // 2. Recent leave history (last 10 days)
        $recentCount = LeaveRequest::where('user_id', $user->id)
                            ->where('status', 'approved')
                            ->whereBetween('start_date', [Carbon::now()->subDays(10)->toDateString(), Carbon::now()->toDateString()])
                            ->count();
        if ($recentCount === 0) {
            $score += 2;
        } else {
            $score -= 1;
            $notes[] = "Recently applied leave in past 10 days ({$recentCount}).";
        }

        // 3. Leave type specific
        if (strtolower($leaveType->name) === 'medical') {
            $score += 1;
            if ($data['file_path']) {
                $score += 3;
            } else {
                // Requires documentation?
                if ($leaveType->requires_documentation) {
                    // If no doc, immediate reject
                    $data['status'] = 'rejected';
                    $data['status_note'] = 'Medical leave requires document.';
                    $leave = LeaveRequest::create($data);
                    return redirect()->route('leave.result', $leave->id)
                                     ->with('error', 'Medical leave: document required.');
                }
            }
        }

        // 4. Blackout period check
        $overlapBlackout = BlackoutPeriod::where(function($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
              ->orWhereBetween('end_date', [$start->toDateString(), $end->toDateString()])
              ->orWhere(function($q2) use ($start, $end) {
                  $q2->where('start_date', '<=', $start->toDateString())
                     ->where('end_date', '>=', $end->toDateString());
              });
        })->exists();
        if ($overlapBlackout) {
            // Immediate reject
            $data['status'] = 'rejected';
            $data['status_note'] = 'Leave falls in blackout period.';
            $leave = LeaveRequest::create($data);
            return redirect()->route('leave.result', $leave->id)
                             ->with('error', 'Requested dates fall in blackout period.');
        }

        // 5. Weekday check (Friday/Monday)
        $weekday = $start->format('l'); // e.g., Monday, Friday
        if (in_array($weekday, ['Monday','Friday'])) {
            $score -= 1;
            $notes[] = "Start on {$weekday}, suspicious.";
        }

        // 6. Collision detection: same-department overlapping approved leaves
        $conflicts = LeaveRequest::where('status', 'approved')
            ->where('department_id', $user->department_id)
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                  ->orWhereBetween('end_date', [$start->toDateString(), $end->toDateString()])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_date', '<=', $start->toDateString())
                         ->where('end_date', '>=', $end->toDateString());
                  });
            })
            ->count();
        if ($conflicts >= 2) {
            $data['status'] = 'rejected';
            $data['status_note'] = 'Departmental collision: too many on leave.';
            $leave = LeaveRequest::create($data);
            return redirect()->route('leave.result', $leave->id)
                             ->with('error', 'Too many colleagues already on leave.');
        } elseif ($conflicts === 1) {
            // Slight penalty
            $score -= 1;
            $notes[] = 'One colleague already on leave.';
        } else {
            $score += 1;
        }

        // Final decision based on score
        $data['final_score'] = $score;
        if ($score >= 2) {
            // Auto approve
            $data['status'] = 'approved';
            $data['status_note'] = 'Auto-approved with score '.$score.'.';
        } else {
            // Auto reject
            $data['status'] = 'rejected';
            $data['status_note'] = 'Auto-rejected with score '.$score.'.';
        }

        // Create leave record
        $leave = LeaveRequest::create($data);

        // If approved: deduct leave credits
        if ($data['status'] === 'approved' && $credit) {
            $credit->remaining_days = max(0, $credit->remaining_days - $days);
            $credit->save();
        }

        // Redirect to result
        if ($data['status'] === 'approved') {
            return redirect()->route('leave.result', $leave->id)
                             ->with('success', 'Leave auto-approved.');
        } else {
            $reasonText = implode(' ', $notes);
            return redirect()->route('leave.result', $leave->id)
                             ->with('error', 'Leave auto-rejected. Reason: '.$data['status_note']);
        }
    }

    // List user's leaves
    public function index()
    {
        $user = Auth::user();
        $leaves = LeaveRequest::where('user_id', $user->id)
                    ->orderBy('start_date','desc')
                    ->get();
        return view('frontend.leave.list', compact('leaves'));
    }

    // Show single leave detail
    public function show($id)
    {
        $user = Auth::user();
        $leave = LeaveRequest::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        return view('frontend.leave.show', compact('leave'));
    }

    // Result view after apply
    public function result($id)
    {
        $user = Auth::user();
        $leave = LeaveRequest::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        return view('frontend.leave.result', compact('leave'));
    }
}
