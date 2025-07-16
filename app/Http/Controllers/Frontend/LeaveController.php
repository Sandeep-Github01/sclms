<?php

namespace App\Http\Controllers\Frontend;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\LeaveCredit;
use App\Models\BlackoutPeriod;
use Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{
    // 1. Show apply form
    public function create()
    {
        $leaveTypes = LeaveType::all();
        $blackouts = BlackoutPeriod::all()->map(function ($b) {
            return [
                'title' => 'Blackout',
                'start' => $b->start_date,
                'end' => $b->end_date,
                'color' => '#000'
            ];
        });

        return view('frontend.leave.apply', compact('leaveTypes', 'blackouts'));
    }

    // 2. Process leave application (validates, evaluates, logs steps)
    public function process(Request $request)
    {
        $user = Auth::user();
        $steps = [];
        $score = 0;

        if (!$user->dept_name) {
            $steps[] = ['text' => "User has no department assigned.", 'score' => $score, 'type' => 'error'];
            return redirect()->back()->withErrors(['error' => 'You must be assigned to a department.'])->withInput();
        }
        $steps[] = ['text' => "Department assigned: {$user->dept_name}", 'score' => $score, 'type' => 'success'];

        $department = Department::where('name', $user->dept_name)->first();
        if (!$department) {
            $steps[] = ['text' => "Department '{$user->dept_name}' not found.", 'score' => $score, 'type' => 'error'];
            return redirect()->back()->withErrors(['error' => 'Your department was not found.'])->withInput();
        }
        $steps[] = ['text' => "Department exists in system.", 'score' => $score, 'type' => 'success'];

        $request->validate([
            'type_id' => 'required|integer|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'document' => 'nullable|file|max:2048',
        ]);
        $steps[] = ['text' => "Form data validated.", 'score' => $score, 'type' => 'success'];

        $leaveType = LeaveType::findOrFail($request->type_id);
        $steps[] = ['text' => "Leave type: {$leaveType->name}", 'score' => $score, 'type' => 'success'];

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $days = $start->diffInDays($end) + 1;
        $steps[] = ['text' => "Duration: {$days} day(s)", 'score' => $score, 'type' => 'success'];

        $data = [
            'user_id' => $user->id,
            'type_id' => $leaveType->id,
            'department_id' => $department->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'reason' => $request->reason,
            'file_path' => null,
            'status' => 'pending',
            'review_type' => 'auto',
            'final_score' => null,
            'status_note' => null,
        ];

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('leave_docs', 'public');
            $data['file_path'] = $path;
            $steps[] = ['text' => "Document uploaded.", 'score' => $score, 'type' => 'document'];
        } else {
            $steps[] = ['text' => "No document uploaded.", 'score' => $score, 'type' => 'document'];
        }

        // Manual Review Rules
        $isManual = false;
        if (strtolower($leaveType->name) === 'emergency') {
            $isManual = true;
            $steps[] = ['text' => "Emergency leave → manual review required.", 'score' => $score, 'type' => 'warning'];
        } elseif ($leaveType->max_days && $days > $leaveType->max_days) {
            $isManual = true;
            $steps[] = ['text' => "Exceeds max allowed days ({$leaveType->max_days}). Manual review.", 'score' => $score, 'type' => 'warning'];
        }

        if ($isManual) {
            $data['review_type'] = 'manual';
            $data['status'] = 'pending';
            $data['status_note'] = 'Manual review required.';
            $leave = LeaveRequest::create($data);
            $steps[] = ['text' => "Leave submitted for manual review.", 'score' => $score, 'type' => 'success'];
            session(['leave_steps' => $steps]);
            return redirect()->route('leave.process.view', $leave->id);
        }

        // Auto Evaluation
        $credit = LeaveCredit::where('user_id', $user->id)->where('type_id', $leaveType->id)->first();
        if ($credit) {
            if ($credit->remaining_days >= $days) {
                $score += 2;
                $steps[] = ['text' => "Sufficient leave credits ({$credit->remaining_days}).", 'score' => $score, 'type' => 'success'];
            } else {
                $score -= 2;
                $steps[] = ['text' => "Insufficient leave credits ({$credit->remaining_days}).", 'score' => $score, 'type' => 'error'];
            }
        } else {
            $score -= 2;
            $steps[] = ['text' => "No leave credit record.", 'score' => $score, 'type' => 'error'];
        }

        $recentCount = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereBetween('start_date', [Carbon::now()->subDays(10)->toDateString(), Carbon::now()->toDateString()])
            ->count();
        if ($recentCount === 0) {
            $score += 2;
            $steps[] = ['text' => "No recent leaves taken.", 'score' => $score, 'type' => 'success'];
        } else {
            $score -= 1;
            $steps[] = ['text' => "{$recentCount} recent leave(s) in past 10 days.", 'score' => $score, 'type' => 'warning'];
        }

        if (strtolower($leaveType->name) === 'medical') {
            $score += 1;
            if ($data['file_path']) {
                $score += 3;
                $steps[] = ['text' => "Medical document attached.", 'score' => $score, 'type' => 'success'];
            } elseif ($leaveType->requires_documentation) {
                $data['status'] = 'rejected';
                $data['status_note'] = 'Medical document required.';
                $leave = LeaveRequest::create($data);
                $steps[] = ['text' => "Rejected: Missing medical document.", 'score' => $score, 'type' => 'error'];
                session(['leave_steps' => $steps]);
                return redirect()->route('leave.process.view', $leave->id);
            }
        }

        $blackout = BlackoutPeriod::where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('start_date', '<=', $start)
                        ->where('end_date', '>=', $end);
                });
        })->exists();

        if ($blackout) {
            $data['status'] = 'rejected';
            $data['status_note'] = 'Falls in blackout period.';
            $leave = LeaveRequest::create($data);
            $steps[] = ['text' => "Rejected due to blackout period.", 'score' => $score, 'type' => 'error'];
            session(['leave_steps' => $steps]);
            return redirect()->route('leave.process.view', $leave->id);
        }
        $steps[] = ['text' => "No blackout conflict.", 'score' => $score, 'type' => 'success'];

        $conflicts = LeaveRequest::where('status', 'approved')
            ->where('department_id', $department->id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })->count();

        if ($conflicts >= 2) {
            $data['status'] = 'rejected';
            $data['status_note'] = 'Too many colleagues already on leave.';
            $leave = LeaveRequest::create($data);
            $steps[] = ['text' => "Rejected: Departmental conflict.", 'score' => $score, 'type' => 'error'];
            session(['leave_steps' => $steps]);
            return redirect()->route('leave.process.view', $leave->id);
        } elseif ($conflicts === 1) {
            $score -= 1;
            $steps[] = ['text' => "One colleague already on leave.", 'score' => $score, 'type' => 'warning'];
        } else {
            $score += 1;
            $steps[] = ['text' => "No conflict in department.", 'score' => $score, 'type' => 'success'];
        }

        $data['final_score'] = $score;
        if ($score >= 2) {
            $data['status'] = 'approved';
            $data['status_note'] = 'Auto-approved with score ' . $score . '.';
            $steps[] = ['text' => "Final decision: Approved", 'score' => $score, 'type' => 'success'];
        } else {
            $data['status'] = 'rejected';
            $data['status_note'] = 'Auto-rejected with score ' . $score . '.';
            $steps[] = ['text' => "Final decision: Rejected", 'score' => $score, 'type' => 'error'];
        }

        $leave = LeaveRequest::create($data);

        if ($data['status'] === 'approved' && $credit) {
            $credit->remaining_days = max(0, $credit->remaining_days - $days);
            $credit->save();
        }

        session(['leave_steps' => $steps]);
        return redirect()->route('leave.process.view', $leave->id);
    }


    // 3. Show the process log
    public function processView($id)
    {
        $leave = LeaveRequest::with('leaveType')->findOrFail($id);
        $steps = session('leave_steps', []);
        return view('frontend.leave.process', compact('leave', 'steps'));
    }

    // 4. Show final result page
    public function result($id)
    {
        $leave = LeaveRequest::with('leaveType')->findOrFail($id);
        return view('frontend.leave.result', compact('leave'));
    }

    public function index()
    {
        $user = Auth::user();
        $leaves = LeaveRequest::where('user_id', $user->id)
            ->orderBy('start_date', 'desc')
            ->get();
        return view('frontend.leave.list', compact('leaves'));
    }
    public function show($id)
    {
        $leave = LeaveRequest::with('leaveType')->findOrFail($id);
        return view('frontend.leave.show', compact('leave'));
    }
}
