<?php

namespace App\Http\Controllers\Frontend;

use Auth;
use Carbon\Carbon;
use App\Models\Admin;
use App\Models\LeaveType;
use App\Models\Department;
use App\Models\LeaveCredit;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Models\BlackoutPeriod;
use App\Mail\LeaveSubmittedMail;
use Illuminate\Support\Facades\DB;
use App\Mail\LeaveManualReviewMail;
use App\Http\Controllers\Controller;
use App\Mail\MedicalDocUploadedMail;
use Illuminate\Support\Facades\Mail;
use App\Services\Leave\LeaveCreditService;
use App\Services\Leave\LeaveConflictService;
use App\Services\Leave\LeaveExecutionService;
use App\Services\Leave\LeaveValidationService;

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

        return redirect()->route('leave.process.view', $final['leave']->id)
            ->with('success', 'Leave request submitted successfully.');
    }

    // 3. Show the process log
    public function processView($id)
    {
        $leave = LeaveRequest::with(['user', 'leaveType', 'department'])->findOrFail($id);

        // Get probability from the database
        $probability = round(($leave->probability ?? 0) * 100, 1);

        // Get steps from database (leave_evaluations table), NOT from session
        $evaluation = DB::table('leave_evaluations')
            ->where('leave_request_id', $leave->id)
            ->first();

        if ($evaluation && $evaluation->steps) {
            $steps = json_decode($evaluation->steps, true);
        } else {
            // Fallback: try session
            $steps = session('leave_steps', []);
            // Clear session after reading to prevent contamination
            session()->forget('leave_steps');
        }

        // Get the final score
        $score = $leave->final_score ?? 0;

        // Determine probability label
        $probLabel = 'Low';
        if ($probability >= 70) {
            $probLabel = 'High';
        } elseif ($probability >= 40) {
            $probLabel = 'Moderate';
        }

        return view('frontend.leave.process', compact('leave', 'steps', 'probability', 'probLabel', 'score'));
    }

    // 4. Show final result page
    public function result($id)
    {
        $leave = LeaveRequest::with('leaveType')->findOrFail($id);
        return view('frontend.leave.result', compact('leave'));
    }

    public function cancel($id)
    {
        $leave = LeaveRequest::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $leave->update(['status' => 'cancelled']);

        return redirect()->route('leave.list')
            ->with('success', 'Leave cancelled successfully.');
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
            ->orderBy('created_at', 'desc')
            ->get();
        return view('frontend.leave.list', compact('leaves'));
    }

    /* ---------- provisional document upload ---------- */
    public function provisionalIndex()
    {
        $leaves = LeaveRequest::where('user_id', Auth::id())
            ->where('status', 'provisional')
            ->where('document_status', 'pending')
            ->with('leaveType')
            ->get();

        return view('frontend.leave.provisional-index', compact('leaves'));
    }

    public function provisionalUploadForm($id)
    {
        $leave = LeaveRequest::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'provisional')
            ->firstOrFail();

        return view('frontend.leave.provisional-upload', compact('leave'));
    }

    public function provisionalUploadStore(Request $request, $id)
    {
        $leave = LeaveRequest::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'provisional')
            ->firstOrFail();

        $request->validate([
            'document' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png'
        ]);

        $path = $request->file('document')->store('leave_docs', 'local');

        $leave->update([
            'file_path' => $path,
            'document_status' => 'submitted'
        ]);

        // notify admins
        foreach (Admin::all() as $admin) {
            Mail::to($admin->email)->send(new MedicalDocUploadedMail($leave));
        }

        return redirect()->route('leave.provisional.index')
            ->with('success', 'Document uploaded. Admin will review soon.');
    }

}
