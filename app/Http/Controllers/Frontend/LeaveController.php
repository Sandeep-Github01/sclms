<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\LeaveCredit;
use App\Models\BlackoutPeriod;
use App\Models\Admin;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\LeaveSubmittedMail;
use App\Mail\LeaveManualReviewMail;
use Illuminate\Support\Facades\DB;
use App\Services\Leave\LeaveValidationService;
use App\Services\Leave\LeaveConflictService;
use App\Services\Leave\LeaveCreditService;
use App\Services\Leave\LeaveExecutionService;

class LeaveController extends Controller
{
    protected $validation;
    protected $conflict;
    protected $credit;
    protected $execution;

    public function __construct(
        LeaveValidationService $validation,
        LeaveConflictService $conflict,
        LeaveCreditService $credit,
        LeaveExecutionService $execution
    ) {
        $this->validation = $validation;
        $this->conflict = $conflict;
        $this->credit = $credit;
        $this->execution = $execution;
    }

    // 1. Show apply form
    public function create()
    {
        $leaveTypes = LeaveType::all();
        $user = Auth::user();

        $department = Department::where('name', $user->dept_name)->first();
        $departmentId = $department ? $department->id : null;

        $deptArr = $departmentId !== null
            ? [(string) $departmentId]
            : [];
        $semArr  = $user->semester
            ? [(string) $user->semester]
            : [];

        $blackouts = BlackoutPeriod::query()
            ->where(function ($q) use ($deptArr) {
                $q->whereNull('department_id');
                if (! empty($deptArr)) {
                    $q->orWhereJsonContains('department_id', $deptArr);
                }
            })
            ->where(function ($q) use ($semArr) {
                $q->whereNull('semester');
                if (! empty($semArr)) {
                    $q->orWhereJsonContains('semester', $semArr);
                }
            })
            ->get()
            ->map(function ($b) {
                return [
                    'title' => 'Blackout',
                    'start' => $b->start_date,
                    'end'   => $b->end_date,
                    'color' => '#000',
                ];
            });

        return view('frontend.leave.apply', compact('leaveTypes', 'blackouts'));
    }

    // 2. Process leave application
    public function process(Request $request)
    {
        // 1. validate basic leave inputs
        $step1 = $this->validation->validateRequest($request);
        if (!isset($step1['success']) || !$step1['success']) {
            $message = $step1['message'] ?? 'Validation failed.';
            return redirect()->back()->withErrors(['error' => $message])->withInput();
        }

        // 2. check conflicts
        $step2 = $this->conflict->checkConflicts($request, $step1);
        if (!isset($step2['success']) || !$step2['success']) {
            $message = $step2['message'] ?? 'Conflict check failed.';
            return redirect()->back()->withErrors(['error' => $message])->withInput();
        }

        // 3. check leave credits
        $step3 = $this->credit->checkCredits($request, $step1, $step2);
        if (!isset($step3['success']) || !$step3['success']) {
            $message = $step3['message'] ?? 'Credit check failed.';
            return redirect()->back()->withErrors(['error' => $message])->withInput();
        }

        // 4. execute leave creation
        $final = $this->execution->createLeave($request, $step1, $step2, $step3);
        if (!isset($final['success']) || !$final['success']) {
            $message = $final['message'] ?? 'Leave creation failed.';
            return redirect()->back()->withErrors(['error' => $message])->withInput();
        }

        // Success: redirect to leave index (as you requested in the sample)
        return redirect()->route('leave.index')
            ->with('success', 'Leave request submitted successfully.');
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

    public function show($id)
    {
        $leave = LeaveRequest::with('leaveType')->findOrFail($id);
        return view('frontend.leave.show', compact('leave'));
    }

    public function index()
    {
        $user = Auth::user();
        $leaves = LeaveRequest::where('user_id', $user->id)
            ->orderBy('start_date', 'desc')
            ->get();
        return view('frontend.leave.list', compact('leaves'));
    }
}


// class LeaveController extends Controller
// {
//     // // 1. Show apply form
    // public function create()
    // {
    //     $leaveTypes = LeaveType::all();
    //     $user = Auth::user();

    //     $department = Department::where('name', $user->dept_name)->first();
    //     $departmentId = $department ? $department->id : null;

    //     $deptArr = $departmentId !== null
    //         ? [(string) $departmentId]
    //         : [];
    //     $semArr  = $user->semester
    //         ? [(string) $user->semester]
    //         : [];

    //     $blackouts = BlackoutPeriod::query()
    //         ->where(function ($q) use ($deptArr) {
    //             $q->whereNull('department_id');
    //             if (! empty($deptArr)) {
    //                 $q->orWhereJsonContains('department_id', $deptArr);
    //             }
    //         })
    //         ->where(function ($q) use ($semArr) {
    //             $q->whereNull('semester');
    //             if (! empty($semArr)) {
    //                 $q->orWhereJsonContains('semester', $semArr);
    //             }
    //         })
    //         ->get()
    //         ->map(function ($b) {
    //             return [
    //                 'title' => 'Blackout',
    //                 'start' => $b->start_date,
    //                 'end'   => $b->end_date,
    //                 'color' => '#000',
    //             ];
    //         });

    //     return view('frontend.leave.apply', compact('leaveTypes', 'blackouts'));
    // }

    // // 2. Process leave application
    // public function process(Request $request)
    // {
    //     $user  = Auth::user();
    //     $steps = [];
    //     $score = 0;

    //     // 1. Department existence
    //     if (! $user->dept_name) {
    //         return redirect()->back()
    //             ->withErrors(['error' => 'You must be assigned to a department.'])
    //             ->withInput();
    //     }
    //     $steps[] = ['text' => "Department assigned: {$user->dept_name}", 'score' => $score, 'type' => 'success'];

    //     $department = Department::where('name', $user->dept_name)->first();
    //     if (! $department) {
    //         return redirect()->back()
    //             ->withErrors(['error' => "Your department '{$user->dept_name}' was not found."])
    //             ->withInput();
    //     }
    //     $steps[] = ['text' => "Department exists in system.", 'score' => $score, 'type' => 'success'];

    //     // 2. Validate inputs
    //     $request->validate([
    //         'type_id'    => 'required|integer|exists:leave_types,id',
    //         'start_date' => 'required|date',
    //         'end_date'   => 'required|date|after_or_equal:start_date',
    //         'reason'     => 'nullable|string',
    //         'document'   => 'nullable|file|max:2048',
    //     ]);
    //     $steps[] = ['text' => "Form data validated.", 'score' => $score, 'type' => 'success'];

    //     $leaveType = LeaveType::findOrFail($request->type_id);
    //     $steps[]   = ['text' => "Leave type: {$leaveType->name}", 'score' => $score, 'type' => 'success'];

    //     // 3. Calculate duration
    //     $start = Carbon::parse($request->start_date);
    //     $end   = Carbon::parse($request->end_date);
    //     $days  = $start->diffInDays($end) + 1;
    //     $steps[] = ['text' => "Duration: {$days} day(s)", 'score' => $score, 'type' => 'success'];

    //     // 4. Prepare base data
    //     $data = [
    //         'user_id'       => $user->id,
    //         'type_id'       => $leaveType->id,
    //         'department_id' => $department->id,
    //         'start_date'    => $start->toDateString(),
    //         'end_date'      => $end->toDateString(),
    //         'reason'        => $request->reason,
    //         'file_path'     => null,
    //         'status'        => 'pending',
    //         'review_type'   => 'auto',
    //         'final_score'   => null,
    //         'status_note'   => null,
    //     ];

    //     // 5. Handle document
    //     if ($request->hasFile('document')) {
    //         $data['file_path'] = $request->file('document')->store('leave_docs', 'public');
    //         $steps[] = ['text' => "Document uploaded.", 'score' => $score, 'type' => 'document'];
    //     } else {
    //         $steps[] = ['text' => "No document uploaded.", 'score' => $score, 'type' => 'document'];
    //     }

    //     // 6. Manual review conditions
    //     $isManual = false;
    //     if (strtolower($leaveType->name) === 'emergency') {
    //         $isManual = true;
    //         $steps[]  = ['text' => "Emergency leave → manual review required.", 'score' => $score, 'type' => 'warning'];
    //     } elseif ($leaveType->max_days && $days > $leaveType->max_days) {
    //         $isManual = true;
    //         $steps[]  = ['text' => "Exceeds max allowed days ({$leaveType->max_days}). Manual review.", 'score' => $score, 'type' => 'warning'];
    //     }

    //     // if ($isManual) {
    //     //     $data['review_type'] = 'manual';
    //     //     $leave = LeaveRequest::create($data);
    //     //     $steps[] = ['text' => "Leave submitted for manual review.", 'score' => $score, 'type' => 'success'];
    //     //     session(['leave_steps' => $steps]);
    //     //     return redirect()->route('leave.process.view', $leave->id);
    //     // }
    //     if ($isManual) {
    //         $data['review_type'] = 'manual';
    //         $leave = LeaveRequest::create($data);

    //         // 1. Send "submitted" mail to user
    //         Mail::to($user->email)->send(new LeaveSubmittedMail($leave));

    //         // 2. Send "manual review required" mail to admin
    //         $admin = Admin::first(); // You can improve this later
    //         if ($admin) {
    //             Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
    //         }

    //         $steps[] = ['text' => "Leave submitted for manual review.", 'score' => $score, 'type' => 'success'];
    //         session(['leave_steps' => $steps]);
    //         return redirect()->route('leave.process.view', $leave->id);
    //     }


    //     // 7. Auto evaluation (credits, recent leaves, medical docs)
    //     $credit = LeaveCredit::where('user_id', $user->id)
    //         ->where('type_id', $leaveType->id)
    //         ->first();

    //     if ($credit) {
    //         if ($credit->remaining_days >= $days) {
    //             $score += 2;
    //             $steps[] = ['text' => "Sufficient leave credits ({$credit->remaining_days}).", 'score' => $score, 'type' => 'success'];
    //         } else {
    //             $score -= 2;
    //             $steps[] = ['text' => "Insufficient leave credits ({$credit->remaining_days}).", 'score' => $score, 'type' => 'error'];
    //         }
    //     } else {
    //         $score -= 2;
    //         $steps[] = ['text' => "No leave credit record.", 'score' => $score, 'type' => 'error'];
    //     }

    //     $recentCount = LeaveRequest::where('user_id', $user->id)
    //         ->where('status', 'approved')
    //         ->whereBetween('start_date', [
    //             Carbon::now()->subDays(10)->toDateString(),
    //             Carbon::now()->toDateString()
    //         ])->count();

    //     if ($recentCount === 0) {
    //         $score += 2;
    //         $steps[] = ['text' => "No recent leaves taken.", 'score' => $score, 'type' => 'success'];
    //     } else {
    //         $score -= 1;
    //         $steps[] = ['text' => "{$recentCount} recent leave(s) in past 10 days.", 'score' => $score, 'type' => 'warning'];
    //     }

    //     if (strtolower($leaveType->name) === 'medical') {
    //         $score += 1;
    //         if ($data['file_path']) {
    //             $score += 3;
    //             $steps[] = ['text' => "Medical document attached.", 'score' => $score, 'type' => 'success'];
    //         } elseif ($leaveType->requires_documentation) {
    //             $data['status']      = 'rejected';
    //             $data['status_note'] = 'Medical document required.';
    //             $leave = LeaveRequest::create($data);
    //             $steps[] = ['text' => "Rejected: Missing medical document.", 'score' => $score, 'type' => 'error'];
    //             session(['leave_steps' => $steps]);
    //             return redirect()->route('leave.process.view', $leave->id);
    //         }
    //     }

    //     // 8. Blackout check with department & semester
    //     $deptArr = [(string)$department->id];
    //     $semArr  = [(string)$user->semester];

    //     $inBlackout = BlackoutPeriod::where(function ($q) use ($start, $end) {
    //         $q->whereBetween('start_date', [$start, $end])
    //             ->orWhereBetween('end_date',   [$start, $end])
    //             ->orWhere(function ($q2) use ($start, $end) {
    //                 $q2->where('start_date', '<=', $start)
    //                     ->where('end_date',   '>=', $end);
    //             });
    //     })
    //         ->where(function ($q) use ($deptArr) {
    //             $q->whereNull('department_id')
    //                 ->orWhereJsonContains('department_id', $deptArr);
    //         })
    //         ->where(function ($q) use ($semArr) {
    //             $q->whereNull('semester')
    //                 ->orWhereJsonContains('semester', $semArr);
    //         })
    //         ->exists();

    //     if ($inBlackout) {
    //         $data['status']      = 'rejected';
    //         $data['status_note'] = 'Falls in blackout period.';
    //         $leave = LeaveRequest::create($data);
    //         $steps[] = ['text' => "Rejected due to blackout period.", 'score' => $score, 'type' => 'error'];
    //         session(['leave_steps' => $steps]);
    //         return redirect()->route('leave.process.view', $leave->id);
    //     }
    //     $steps[] = ['text' => "No blackout conflict.", 'score' => $score, 'type' => 'success'];

    //     // 9. Departmental conflict by role & semester
    //     $role     = $user->role;       // 'student' or 'teacher'
    //     $semester = $user->semester;   // used only for students

    //     $conflictQuery = LeaveRequest::where('status', 'approved')
    //         ->where('department_id', $department->id)
    //         ->where('role', $role)
    //         ->where(function ($q) use ($start, $end) {
    //             $q->whereBetween('start_date', [$start, $end])
    //                 ->orWhereBetween('end_date',   [$start, $end])
    //                 ->orWhere(function ($q2) use ($start, $end) {
    //                     $q2->where('start_date', '<=', $start)
    //                         ->where('end_date',   '>=', $end);
    //                 });
    //         });

    //     if ($role === 'student') {
    //         $conflictQuery->where('semester', $semester);
    //     }

    //     $conflicts = $conflictQuery->count();

    //     if ($role === 'teacher') {
    //         if ($conflicts >= 1) {
    //             $data['status']      = 'rejected';
    //             $data['status_note'] = 'Departmental conflict: teacher absent.';
    //             $leave = LeaveRequest::create($data);
    //             $steps[] = ['text' => "Rejected: Another teacher already on leave.", 'score' => $score, 'type' => 'error'];
    //             session(['leave_steps' => $steps]);
    //             return redirect()->route('leave.process.view', $leave->id);
    //         } else {
    //             $score += 1;
    //             $steps[] = ['text' => "No teacher conflict.", 'score' => $score, 'type' => 'success'];
    //         }
    //     } else { // student
    //         if ($conflicts >= 3) {
    //             $data['status']      = 'rejected';
    //             $data['status_note'] = 'Departmental conflict: too many students absent.';
    //             $leave = LeaveRequest::create($data);
    //             $steps[] = ['text' => "Rejected: {$conflicts} peers already on leave.", 'score' => $score, 'type' => 'error'];
    //             session(['leave_steps' => $steps]);
    //             return redirect()->route('leave.process.view', $leave->id);
    //         } elseif ($conflicts === 2) {
    //             $score -= 2;
    //             $steps[] = ['text' => "2 peers in your semester already on leave → warning.", 'score' => $score, 'type' => 'warning'];
    //         } elseif ($conflicts === 1) {
    //             $score -= 1;
    //             $steps[] = ['text' => "1 peer in your semester already on leave → warning.", 'score' => $score, 'type' => 'warning'];
    //         } else {
    //             $score += 1;
    //             $steps[] = ['text' => "No peer conflict in your semester.", 'score' => $score, 'type' => 'success'];
    //         }
    //     }

    //     // 10. Final scoring and creation
    //     $data['final_score'] = $score;
    //     if ($score >= 2) {
    //         $data['status']      = 'approved';
    //         $data['status_note'] = 'Auto-approved with score ' . $score . '.';
    //         $steps[] = ['text' => "Final decision: Approved", 'score' => $score, 'type' => 'success'];
    //     } else {
    //         $data['status']      = 'rejected';
    //         $data['status_note'] = 'Auto-rejected with score ' . $score . '.';
    //         $steps[] = ['text' => "Final decision: Rejected", 'score' => $score, 'type' => 'error'];
    //     }

    //     $leave = LeaveRequest::create($data);

    //     // Deduct credits if approved
    //     if ($data['status'] === 'approved' && isset($credit)) {
    //         $credit->remaining_days = max(0, $credit->remaining_days - $days);
    //         $credit->save();
    //     }

    //     session(['leave_steps' => $steps]);
    //     return redirect()->route('leave.process.view', $leave->id);
    // }



    // public function process(Request $request)
    // {
    //     $user  = Auth::user();
    //     $steps = [];
    //     $score = 0;
    //     $isManual = false;

    //     // -------------------------
    //     // 1. Department existence
    //     // -------------------------
    //     if (! $user->dept_name) {
    //         return redirect()->back()
    //             ->withErrors(['error' => 'You must be assigned to a department.'])
    //             ->withInput();
    //     }
    //     $steps[] = ['text' => "Department assigned: {$user->dept_name}", 'score' => $score, 'type' => 'success'];

    //     $department = Department::where('name', $user->dept_name)->first();
    //     if (! $department) {
    //         return redirect()->back()
    //             ->withErrors(['error' => "Your department '{$user->dept_name}' was not found."])
    //             ->withInput();
    //     }
    //     $steps[] = ['text' => "Department exists in system.", 'score' => $score, 'type' => 'success'];

    //     // -------------------------
    //     // 2. Validate inputs
    //     // -------------------------
    //     $request->validate([
    //         'type_id'    => 'required|integer|exists:leave_types,id',
    //         'start_date' => 'required|date',
    //         'end_date'   => 'required|date|after_or_equal:start_date',
    //         'reason'     => 'nullable|string',
    //         'document'   => 'nullable|file|max:2048',
    //     ]);
    //     $steps[] = ['text' => "Form data validated.", 'score' => $score, 'type' => 'success'];

    //     $leaveType = LeaveType::findOrFail($request->type_id);
    //     $steps[]  = ['text' => "Leave type: {$leaveType->name}", 'score' => $score, 'type' => 'success'];

    //     // -------------------------
    //     // 3. Calculate duration
    //     // -------------------------
    //     $start = Carbon::parse($request->start_date);
    //     $end   = Carbon::parse($request->end_date);
    //     $days  = $start->diffInDays($end) + 1;
    //     $steps[] = ['text' => "Duration: {$days} day(s)", 'score' => $score, 'type' => 'success'];

    //     // -------------------------
    //     // 4. Fraud detection / manual triggers
    //     // -------------------------
    //     $fraudScore = 0;
    //     $fraudReasons = [];

    //     // 4a. Weekend pattern
    //     $startWeekday = strtolower($start->format('l'));
    //     $endWeekday   = strtolower($end->format('l'));
    //     if (in_array($startWeekday, ['monday']) || in_array($endWeekday, ['friday'])) {
    //         $fraudScore += 1;
    //         $fraudReasons[] = "Weekend-adjacent pattern detected.";
    //     }

    //     // 4b. Multiple emergency leaves last 60 days
    //     $emergencyCount = LeaveRequest::where('user_id', $user->id)
    //         ->whereHas('leaveType', fn($q) => $q->whereRaw("LOWER(name)='emergency'"))
    //         ->whereBetween('start_date', [Carbon::now()->subDays(60)->toDateString(), Carbon::now()->toDateString()])
    //         ->count();
    //     if ($emergencyCount >= 2) {
    //         $fraudScore += 3;
    //         $fraudReasons[] = "Multiple emergency leaves ({$emergencyCount}) in last 60 days.";
    //     }

    //     // 4c. Duration outlier
    //     $avgDuration = (float) LeaveRequest::where('user_id', $user->id)
    //         ->select(DB::raw('AVG(DATEDIFF(end_date, start_date)+1) as avg_days'))
    //         ->value('avg_days');
    //     if ($avgDuration && $days > ($avgDuration * 2)) {
    //         $fraudScore += 2;
    //         $fraudReasons[] = "Duration unusually long ({$days} days vs average " . round($avgDuration, 2) . ").";
    //     }

    //     // 4d. High frequency leaves last 30 days
    //     $freqCount30 = LeaveRequest::where('user_id', $user->id)
    //         ->whereBetween('start_date', [Carbon::now()->subDays(30)->toDateString(), Carbon::now()->toDateString()])
    //         ->count();
    //     if ($freqCount30 >= 5) {
    //         $fraudScore += 2;
    //         $fraudReasons[] = "High leave frequency ({$freqCount30}) in last 30 days.";
    //     }

    //     $isFraud = $fraudScore >= 4;
    //     if ($isFraud) {
    //         $steps[] = ['text' => "Potential fraud detected: " . implode('; ', $fraudReasons), 'score' => $score, 'type' => 'warning'];
    //         $isManual = true;
    //     }

    //     // -------------------------
    //     // 5. Prepare leave data
    //     // -------------------------
    //     $data = [
    //         'user_id'       => $user->id,
    //         'type_id'       => $leaveType->id,
    //         'department_id' => $department->id,
    //         'start_date'    => $start->toDateString(),
    //         'end_date'      => $end->toDateString(),
    //         'reason'        => $request->reason,
    //         'file_path'     => null,
    //         'status'        => 'pending',
    //         'review_type'   => 'auto',
    //         'final_score'   => null,
    //         'status_note'   => null,
    //         'role'          => $user->role,
    //         'semester'      => $user->semester,
    //     ];

    //     // -------------------------
    //     // 6. Handle document
    //     // -------------------------
    //     if ($request->hasFile('document')) {
    //         $data['file_path'] = $request->file('document')->store('leave_docs', 'public');
    //         $steps[] = ['text' => "Document uploaded.", 'score' => $score, 'type' => 'document'];
    //     } else {
    //         $steps[] = ['text' => "No document uploaded.", 'score' => $score, 'type' => 'document'];
    //     }

    //     // -------------------------
    //     // 7. Manual review triggers
    //     // -------------------------
    //     if (strtolower($leaveType->name) === 'emergency') {
    //         $isManual = true;
    //         $steps[] = ['text' => "Emergency leave → manual review.", 'score' => $score, 'type' => 'warning'];
    //     } elseif ($leaveType->max_days && $days > $leaveType->max_days) {
    //         $isManual = true;
    //         $steps[] = ['text' => "Exceeds max allowed days ({$leaveType->max_days}) → manual review.", 'score' => $score, 'type' => 'warning'];
    //     }

    //     // -------------------------
    //     // 8. Handle manual review
    //     // -------------------------
    //     if ($isManual) {
    //         $data['review_type'] = 'manual';
    //         $leave = LeaveRequest::create($data);

    //         // Notify user
    //         Mail::to($user->email)->send(new LeaveSubmittedMail($leave));

    //         // Notify admin
    //         $admin = Admin::first();
    //         if ($admin) {
    //             Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
    //         }

    //         $steps[] = ['text' => "Leave submitted for manual review.", 'score' => $score, 'type' => 'success'];
    //         session(['leave_steps' => $steps]);
    //         return redirect()->route('leave.process.view', $leave->id);
    //     }

    //     // -------------------------
    //     // 9. Auto evaluation (credits, recent leaves, documents)
    //     // -------------------------
    //     $credit = LeaveCredit::where('user_id', $user->id)->where('type_id', $leaveType->id)->first();
    //     if ($credit && $credit->remaining_days >= $days) {
    //         $score += 2;
    //         $steps[] = ['text' => "Sufficient leave credits ({$credit->remaining_days}).", 'score' => $score, 'type' => 'success'];
    //     } else {
    //         $score -= 2;
    //         $steps[] = ['text' => "Insufficient leave credits or record missing.", 'score' => $score, 'type' => 'error'];
    //     }

    //     $recentCount = LeaveRequest::where('user_id', $user->id)
    //         ->where('status', 'approved')
    //         ->whereBetween('start_date', [Carbon::now()->subDays(10)->toDateString(), Carbon::now()->toDateString()])
    //         ->count();
    //     $score += ($recentCount === 0) ? 2 : -1;
    //     $steps[] = ['text' => ($recentCount === 0 ? "No recent leaves." : "{$recentCount} recent leave(s) in past 10 days."), 'score' => $score, 'type' => ($recentCount === 0 ? 'success' : 'warning')];

    //     // Medical doc check
    //     if (strtolower($leaveType->name) === 'medical') {
    //         $score += 1;
    //         if ($data['file_path']) {
    //             $score += 3;
    //             $steps[] = ['text' => "Medical document attached.", 'score' => $score, 'type' => 'success'];
    //         } elseif ($leaveType->requires_documentation) {
    //             $data['status'] = 'rejected';
    //             $data['status_note'] = "Medical document required.";
    //             $leave = LeaveRequest::create($data);
    //             $steps[] = ['text' => "Rejected: Missing medical document.", 'score' => $score, 'type' => 'error'];
    //             session(['leave_steps' => $steps]);
    //             return redirect()->route('leave.process.view', $leave->id);
    //         }
    //     }

    //     // -------------------------
    //     // 10. Blackout period check
    //     // -------------------------
    //     $deptArr = [(string)$department->id];
    //     $semArr  = [(string)$user->semester];
    //     $inBlackout = BlackoutPeriod::where(function ($q) use ($start, $end) {
    //         $q->whereBetween('start_date', [$start, $end])
    //             ->orWhereBetween('end_date', [$start, $end])
    //             ->orWhere(function ($q2) use ($start, $end) {
    //                 $q2->where('start_date', '<=', $start)->where('end_date', '>=', $end);
    //             });
    //     })->where(function ($q) use ($deptArr) {
    //         $q->whereNull('department_id')->orWhereJsonContains('department_id', $deptArr);
    //     })->where(function ($q) use ($semArr) {
    //         $q->whereNull('semester')->orWhereJsonContains('semester', $semArr);
    //     })->exists();
    //     if ($inBlackout) {
    //         $data['status'] = 'rejected';
    //         $data['status_note'] = 'Falls in blackout period.';
    //         $leave = LeaveRequest::create($data);
    //         $steps[] = ['text' => "Rejected due to blackout period.", 'score' => $score, 'type' => 'error'];
    //         session(['leave_steps' => $steps]);
    //         return redirect()->route('leave.process.view', $leave->id);
    //     }
    //     $steps[] = ['text' => "No blackout conflict.", 'score' => $score, 'type' => 'success'];

    //     // -------------------------
    //     // 11. Department load & conflicts
    //     // -------------------------
    //     $role     = $user->role;
    //     $semester = $user->semester;
    //     $current = $start->copy();
    //     $dailyDetails = [];
    //     $maxAbsentees = 0;
    //     while ($current->lte($end)) {
    //         $d = $current->toDateString();
    //         $dailyCountQuery = LeaveRequest::where('status', 'approved')->where('department_id', $department->id)->whereDate('start_date', '<=', $d)->whereDate('end_date', '>=', $d)->where('role', $role);
    //         if ($role === 'student' && $semester) $dailyCountQuery->where('semester', $semester);
    //         $dailyCount = $dailyCountQuery->count();
    //         $dailyDetails[$d] = $dailyCount;
    //         if ($dailyCount > $maxAbsentees) $maxAbsentees = $dailyCount;
    //         $current->addDay();
    //     }

    //     $teacher_threshold = config('leave.thresholds.teacher', 1);
    //     $student_threshold = config('leave.thresholds.student', 3);
    //     $threshold = $role === 'teacher' ? $teacher_threshold : $student_threshold;
    //     $riskRatio = $maxAbsentees / max(1, $threshold);

    //     if ($riskRatio > 1.5) {
    //         $steps[] = ['text' => "Department critically understaffed (max_absentees={$maxAbsentees})", 'score' => $score, 'type' => 'error'];
    //         if ($role === 'teacher') {
    //             $data['status'] = 'rejected';
    //             $data['status_note'] = 'Department critically understaffed.';
    //             $leave = LeaveRequest::create($data);
    //             $steps[] = ['text' => "Rejected: Department critically understaffed.", 'score' => $score, 'type' => 'error'];
    //             session(['leave_steps' => $steps]);
    //             return redirect()->route('leave.process.view', $leave->id);
    //         } else {
    //             $score -= 2;
    //             $steps[] = ['text' => "High department load risk (students) → manual review.", 'score' => $score, 'type' => 'warning'];
    //             $data['review_type'] = 'manual';
    //             $leave = LeaveRequest::create($data);
    //             Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
    //             $admin = Admin::first();
    //             if ($admin) Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
    //             $steps[] = ['text' => "Leave submitted for manual review due to department load risk.", 'score' => $score, 'type' => 'success'];
    //             session(['leave_steps' => $steps]);
    //             return redirect()->route('leave.process.view', $leave->id);
    //         }
    //     }

    //     // Conflict checks
    //     $conflictsQuery = LeaveRequest::where('status', 'approved')->where('department_id', $department->id)->where('role', $role)->where(function ($q) use ($start, $end) {
    //         $q->whereBetween('start_date', [$start, $end])->orWhereBetween('end_date', [$start, $end])->orWhere(function ($q2) use ($start, $end) {
    //             $q2->where('start_date', '<=', $start)->where('end_date', '>=', $end);
    //         });
    //     });
    //     if ($role === 'student') $conflictsQuery->where('semester', $semester);
    //     $conflicts = $conflictsQuery->count();

    //     if ($role === 'teacher') {
    //         if ($conflicts >= 1) {
    //             $data['status'] = 'rejected';
    //             $data['status_note'] = 'Departmental conflict: teacher absent.';
    //             $leave = LeaveRequest::create($data);
    //             $steps[] = ['text' => "Rejected: Another teacher already on leave.", 'score' => $score, 'type' => 'error'];
    //             session(['leave_steps' => $steps]);
    //             return redirect()->route('leave.process.view', $leave->id);
    //         } else {
    //             $score += 1;
    //             $steps[] = ['text' => "No teacher conflict.", 'score' => $score, 'type' => 'success'];
    //         }
    //     } else {
    //         if ($conflicts >= 3) {
    //             $data['status'] = 'rejected';
    //             $data['status_note'] = 'Departmental conflict: too many students absent.';
    //             $leave = LeaveRequest::create($data);
    //             $steps[] = ['text' => "Rejected: {$conflicts} peers already on leave.", 'score' => $score, 'type' => 'error'];
    //             session(['leave_steps' => $steps]);
    //             return redirect()->route('leave.process.view', $leave->id);
    //         } elseif ($conflicts === 2) {
    //             $score -= 2;
    //             $steps[] = ['text' => "2 peers already on leave → warning.", 'score' => $score, 'type' => 'warning'];
    //         } elseif ($conflicts === 1) {
    //             $score -= 1;
    //             $steps[] = ['text' => "1 peer already on leave → warning.", 'score' => $score, 'type' => 'warning'];
    //         } else {
    //             $score += 1;
    //             $steps[] = ['text' => "No peer conflict.", 'score' => $score, 'type' => 'success'];
    //         }
    //     }

    //     // -------------------------
    //     // 12. Approval predictor
    //     // -------------------------
    //     $credit_ok = ($credit && $credit->remaining_days >= $days) ? 1 : 0;
    //     $recent_feat = ($recentCount === 0) ? 0 : $recentCount;
    //     $doc_present = !empty($data['file_path']) ? 1 : 0;
    //     $conflict_feat = $conflicts;
    //     $days_feat = $days;
    //     $type_priority = strtolower($leaveType->name) === 'medical' ? 2 : 1;

    //     $weights = [
    //         'bias' => -1.0,
    //         'credit_ok' => 1.5,
    //         'recent_count' => -0.6,
    //         'doc_present' => 1.0,
    //         'conflict_count' => -1.2,
    //         'days' => -0.05,
    //         'type_priority' => 0.8,
    //     ];

    //     $z = $weights['bias'] + $weights['credit_ok'] * $credit_ok + $weights['recent_count'] * $recent_feat
    //         + $weights['doc_present'] * $doc_present + $weights['conflict_count'] * $conflict_feat
    //         + $weights['days'] * $days_feat + $weights['type_priority'] * $type_priority;

    //     $probability = 1 / (1 + exp(-$z));
    //     $steps[] = ['text' => "Approval probability (model) calculated.", 'score' => $score, 'type' => 'info'];

    //     // -------------------------
    //     // 13. Final status & notes
    //     // -------------------------
    //     $finalStatus = null;
    //     $finalReviewType = $data['review_type'] ?? 'auto';
    //     $statusNoteParts = [];

    //     if ($probability >= 0.8 && $score >= 0) {
    //         $finalStatus = 'approved';
    //         $statusNoteParts[] = 'Auto-approved';
    //     } elseif ($probability >= 0.5 || ($score >= 0 && $probability >= 0.45)) {
    //         $finalStatus = 'pending';
    //         $finalReviewType = 'manual';
    //         $statusNoteParts[] = 'Manual review';
    //     } else {
    //         $finalStatus = 'rejected';
    //         $statusNoteParts[] = 'Auto-rejected';
    //     }

    //     if (!empty($fraudReasons)) $statusNoteParts[] = "Fraud checks: " . implode('; ', $fraudReasons);

    //     $data['final_score'] = $score;
    //     $data['status'] = $finalStatus;
    //     $data['review_type'] = $finalReviewType;
    //     $data['status_note'] = implode(' | ', $statusNoteParts);

    //     if ($data['status'] === 'approved') $steps[] = ['text' => "Final decision: Approved", 'score' => $score, 'type' => 'success'];
    //     elseif ($data['status'] === 'pending') $steps[] = ['text' => "Final decision: Pending / Manual review", 'score' => $score, 'type' => 'warning'];
    //     else $steps[] = ['text' => "Final decision: Rejected", 'score' => $score, 'type' => 'error'];

    //     $leave = LeaveRequest::create($data);

    //     // Deduct credits if approved
    //     if ($data['status'] === 'approved' && isset($credit)) {
    //         $credit->remaining_days = max(0, $credit->remaining_days - $days);
    //         $credit->save();
    //     }

    //     // Send user/admin emails
    //     if ($data['status'] === 'approved' || $data['status'] === 'rejected') {
    //         Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
    //     } elseif ($data['review_type'] === 'manual') {
    //         Mail::to($user->email)->send(new LeaveSubmittedMail($leave));
    //         $admin = Admin::first();
    //         if ($admin) Mail::to($admin->email)->send(new LeaveManualReviewMail($leave));
    //     }

    //     session(['leave_steps' => $steps]);
    //     return redirect()->route('leave.process.view', $leave->id);
    // }


    // // 3. Show the process log
    // public function processView($id)
    // {
    //     $leave = LeaveRequest::with('leaveType')->findOrFail($id);
    //     $steps = session('leave_steps', []);
    //     return view('frontend.leave.process', compact('leave', 'steps'));
    // }

    // // 4. Show final result page
    // public function result($id)
    // {
    //     $leave = LeaveRequest::with('leaveType')->findOrFail($id);
    //     return view('frontend.leave.result', compact('leave'));
    // }

    // public function show($id)
    // {
    //     $leave = LeaveRequest::with('leaveType')->findOrFail($id);
    //     return view('frontend.leave.show', compact('leave'));
    // }

    // public function index()
    // {
    //     $user = Auth::user();
    //     $leaves = LeaveRequest::where('user_id', $user->id)
    //         ->orderBy('start_date', 'desc')
    //         ->get();
    //     return view('frontend.leave.list', compact('leaves'));
    // }

// }


    // public function process(Request $request)
    // {
    //     $user  = Auth::user();
    //     $steps = [];
    //     $score = 0;

    //     // 1. Department existence
    //     if (! $user->dept_name) {
    //         return redirect()->back()
    //             ->withErrors(['error' => 'You must be assigned to a department.'])
    //             ->withInput();
    //     }
    //     $steps[] = ['text' => "Department assigned: {$user->dept_name}", 'score' => $score, 'type' => 'success'];

    //     $department = Department::where('name', $user->dept_name)->first();
    //     if (! $department) {
    //         return redirect()->back()
    //             ->withErrors(['error' => "Your department '{$user->dept_name}' was not found."])
    //             ->withInput();
    //     }
    //     $steps[] = ['text' => "Department exists in system.", 'score' => $score, 'type' => 'success'];

    //     // 2. Validate inputs
    //     $request->validate([
    //         'type_id'    => 'required|integer|exists:leave_types,id',
    //         'start_date' => 'required|date',
    //         'end_date'   => 'required|date|after_or_equal:start_date',
    //         'reason'     => 'nullable|string',
    //         'document'   => 'nullable|file|max:2048',
    //     ]);
    //     $steps[] = ['text' => "Form data validated.", 'score' => $score, 'type' => 'success'];

    //     $leaveType = LeaveType::findOrFail($request->type_id);
    //     $steps[]   = ['text' => "Leave type: {$leaveType->name}", 'score' => $score, 'type' => 'success'];

    //     // 3. Calculate duration
    //     $start = Carbon::parse($request->start_date);
    //     $end   = Carbon::parse($request->end_date);
    //     $days  = $start->diffInDays($end) + 1;
    //     $steps[] = ['text' => "Duration: {$days} day(s)", 'score' => $score, 'type' => 'success'];

    //     // 4. Prepare data
    //     $data = [
    //         'user_id'       => $user->id,
    //         'type_id'       => $leaveType->id,
    //         'department_id' => $department->id,
    //         'start_date'    => $start->toDateString(),
    //         'end_date'      => $end->toDateString(),
    //         'reason'        => $request->reason,
    //         'file_path'     => null,
    //         'status'        => 'pending',
    //         'review_type'   => 'auto',
    //         'final_score'   => null,
    //         'status_note'   => null,
    //     ];

    //     // 5. Handle document
    //     if ($request->hasFile('document')) {
    //         $data['file_path'] = $request->file('document')->store('leave_docs', 'public');
    //         $steps[] = ['text' => "Document uploaded.", 'score' => $score, 'type' => 'document'];
    //     } else {
    //         $steps[] = ['text' => "No document uploaded.", 'score' => $score, 'type' => 'document'];
    //     }

    //     // 6. Manual review conditions
    //     $isManual = false;
    //     if (strtolower($leaveType->name) === 'emergency') {
    //         $isManual = true;
    //         $steps[]  = ['text' => "Emergency leave → manual review required.", 'score' => $score, 'type' => 'warning'];
    //     } elseif ($leaveType->max_days && $days > $leaveType->max_days) {
    //         $isManual = true;
    //         $steps[]  = ['text' => "Exceeds max allowed days ({$leaveType->max_days}). Manual review.", 'score' => $score, 'type' => 'warning'];
    //     }

    //     if ($isManual) {
    //         $data['review_type'] = 'manual';
    //         $leave = LeaveRequest::create($data);
    //         $steps[] = ['text' => "Leave submitted for manual review.", 'score' => $score, 'type' => 'success'];
    //         session(['leave_steps' => $steps]);
    //         return redirect()->route('leave.process.view', $leave->id);
    //     }

    //     // 7. Auto evaluation (credits, recent leaves, medical docs)
    //     $credit = LeaveCredit::where('user_id', $user->id)
    //         ->where('type_id', $leaveType->id)
    //         ->first();

    //     if ($credit) {
    //         if ($credit->remaining_days >= $days) {
    //             $score += 2;
    //             $steps[] = ['text' => "Sufficient leave credits ({$credit->remaining_days}).", 'score' => $score, 'type' => 'success'];
    //         } else {
    //             $score -= 2;
    //             $steps[] = ['text' => "Insufficient leave credits ({$credit->remaining_days}).", 'score' => $score, 'type' => 'error'];
    //         }
    //     } else {
    //         $score -= 2;
    //         $steps[] = ['text' => "No leave credit record.", 'score' => $score, 'type' => 'error'];
    //     }

    //     $recentCount = LeaveRequest::where('user_id', $user->id)
    //         ->where('status', 'approved')
    //         ->whereBetween('start_date', [
    //             Carbon::now()->subDays(10)->toDateString(),
    //             Carbon::now()->toDateString()
    //         ])->count();

    //     if ($recentCount === 0) {
    //         $score += 2;
    //         $steps[] = ['text' => "No recent leaves taken.", 'score' => $score, 'type' => 'success'];
    //     } else {
    //         $score -= 1;
    //         $steps[] = ['text' => "{$recentCount} recent leave(s) in past 10 days.", 'score' => $score, 'type' => 'warning'];
    //     }

    //     if (strtolower($leaveType->name) === 'medical') {
    //         $score += 1;
    //         if ($data['file_path']) {
    //             $score += 3;
    //             $steps[] = ['text' => "Medical document attached.", 'score' => $score, 'type' => 'success'];
    //         } elseif ($leaveType->requires_documentation) {
    //             $data['status']      = 'rejected';
    //             $data['status_note'] = 'Medical document required.';
    //             $leave = LeaveRequest::create($data);
    //             $steps[] = ['text' => "Rejected: Missing medical document.", 'score' => $score, 'type' => 'error'];
    //             session(['leave_steps' => $steps]);
    //             return redirect()->route('leave.process.view', $leave->id);
    //         }
    //     }

    //     // 8. Blackout check with department & semester
    //     $deptArr = [(string)$department->id];
    //     $semArr  = [(string)$user->semester];

    //     $inBlackout = BlackoutPeriod::where(function ($q) use ($start, $end) {
    //         $q->whereBetween('start_date', [$start, $end])
    //             ->orWhereBetween('end_date',   [$start, $end])
    //             ->orWhere(function ($q2) use ($start, $end) {
    //                 $q2->where('start_date', '<=', $start)
    //                     ->where('end_date',   '>=', $end);
    //             });
    //     })
    //         ->where(function ($q) use ($deptArr) {
    //             $q->whereNull('department_id')
    //                 ->orWhereJsonContains('department_id', $deptArr);
    //         })
    //         ->where(function ($q) use ($semArr) {
    //             $q->whereNull('semester')
    //                 ->orWhereJsonContains('semester', $semArr);
    //         })
    //         ->exists();

    //     if ($inBlackout) {
    //         $data['status']      = 'rejected';
    //         $data['status_note'] = 'Falls in blackout period.';
    //         $leave = LeaveRequest::create($data);
    //         $steps[] = ['text' => "Rejected due to blackout period.", 'score' => $score, 'type' => 'error'];
    //         session(['leave_steps' => $steps]);
    //         return redirect()->route('leave.process.view', $leave->id);
    //     }
    //     $steps[] = ['text' => "No blackout conflict.", 'score' => $score, 'type' => 'success'];

    //     // 9. Departmental conflict check
    //     $conflicts = LeaveRequest::where('status', 'approved')
    //         ->where('department_id', $department->id)
    //         ->where(function ($q) use ($start, $end) {
    //             $q->whereBetween('start_date', [$start, $end])
    //                 ->orWhereBetween('end_date',   [$start, $end])
    //                 ->orWhere(function ($q2) use ($start, $end) {
    //                     $q2->where('start_date', '<=', $start)
    //                         ->where('end_date',   '>=', $end);
    //                 });
    //         })->count();

    //     if ($conflicts >= 2) {
    //         $data['status']      = 'rejected';
    //         $data['status_note'] = 'Too many colleagues already on leave.';
    //         $leave = LeaveRequest::create($data);
    //         $steps[] = ['text' => "Rejected: Departmental conflict.", 'score' => $score, 'type' => 'error'];
    //         session(['leave_steps' => $steps]);
    //         return redirect()->route('leave.process.view', $leave->id);
    //     } elseif ($conflicts === 1) {
    //         $score -= 1;
    //         $steps[] = ['text' => "One colleague already on leave.", 'score' => $score, 'type' => 'warning'];
    //     } else {
    //         $score += 1;
    //         $steps[] = ['text' => "No conflict in department.", 'score' => $score, 'type' => 'success'];
    //     }

    //     // 10. Final scoring and save
    //     $data['final_score'] = $score;
    //     if ($score >= 2) {
    //         $data['status']      = 'approved';
    //         $data['status_note'] = 'Auto-approved with score ' . $score . '.';
    //         $steps[] = ['text' => "Final decision: Approved", 'score' => $score, 'type' => 'success'];
    //     } else {
    //         $data['status']      = 'rejected';
    //         $data['status_note'] = 'Auto-rejected with score ' . $score . '.';
    //         $steps[] = ['text' => "Final decision: Rejected", 'score' => $score, 'type' => 'error'];
    //     }

    //     $leave = LeaveRequest::create($data);

    //     // Deduct credits if approved
    //     if ($data['status'] === 'approved' && isset($credit)) {
    //         $credit->remaining_days = max(0, $credit->remaining_days - $days);
    //         $credit->save();
    //     }

    //     session(['leave_steps' => $steps]);
    //     return redirect()->route('leave.process.view', $leave->id);
    // }