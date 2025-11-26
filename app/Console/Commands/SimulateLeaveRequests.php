<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\Department;
use App\Services\Leave\LeaveValidationService;
use App\Services\Leave\LeaveConflictService;
use App\Services\Leave\LeaveCreditService;
use App\Services\Leave\LeaveExecutionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SimulateLeaveRequests extends Command
{
    protected $signature = 'simulate:leaves
                            {count=50 : Number of leave requests to generate}
                            {--user-id= : Specific user ID (optional)}
                            {--type= : Specific leave type name (optional)}';

    protected $description = 'Simulate realistic leave requests with full business logic';

    protected $validationService;
    protected $conflictService;
    protected $creditService;
    protected $executionService;

    public function __construct(
        LeaveValidationService $validation,
        LeaveConflictService $conflict,
        LeaveCreditService $credit,
        LeaveExecutionService $execution
    ) {
        parent::__construct();
        $this->validationService = $validation;
        $this->conflictService = $conflict;
        $this->creditService = $credit;
        $this->executionService = $execution;
    }

    public function handle()
    {
        $count = $this->argument('count');
        $userId = $this->option('user-id');
        $typeName = $this->option('type');

        $this->info("🚀 Starting simulation of {$count} leave requests...");
        $this->newLine();

        $users = $userId
            ? User::where('id', $userId)->get()
            : User::where('status', 'active')->get();

        if ($users->isEmpty()) {
            $this->error('❌ No active users found!');
            return 1;
        }

        $leaveTypes = $typeName
            ? LeaveType::where('name', $typeName)->get()
            : LeaveType::all();

        if ($leaveTypes->isEmpty()) {
            $this->error('❌ No leave types found!');
            return 1;
        }

        $this->info("👥 Users: {$users->count()}");
        $this->info("📋 Leave Types: {$leaveTypes->pluck('name')->implode(', ')}");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $results = [
            'approved' => 0,
            'rejected' => 0,
            'pending' => 0,
            'provisional' => 0,
            'errors' => 0,
        ];

        for ($i = 0; $i < $count; $i++) {
            try {
                $result = $this->createSingleLeave($users->random(), $leaveTypes->random());

                if (isset($result['leave'])) {
                    $status = $result['leave']->status;
                    $results[$status] = ($results[$status] ?? 0) + 1;
                } else {
                    $results['errors']++;
                }

                $progressBar->advance();
            } catch (\Exception $e) {
                $results['errors']++;
                $progressBar->advance();
                $this->newLine();
                $this->error("Error: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->info('✅ Simulation Complete!');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Approved', $results['approved']],
                ['❌ Rejected', $results['rejected']],
                ['⏳ Pending (Manual)', $results['pending']],
                ['📝 Provisional', $results['provisional']],
                ['⚠️  Errors', $results['errors']],
            ]
        );

        return 0;
    }

    protected function createSingleLeave($user, $leaveType)
    {
        // Simulate login
        Auth::login($user);

        // Generate realistic dates
        $scenario = $this->generateScenario($leaveType);

        // Validate dates before proceeding
        $startDate = \Carbon\Carbon::parse($scenario['start_date']);
        $endDate = \Carbon\Carbon::parse($scenario['end_date']);

        // Ensure end date is not before start date
        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy()->addDays(rand(1, 3));
        }

        // Create fake request object
        $requestData = [
            'type_id' => $leaveType->id,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'reason' => $scenario['reason'],
        ];

        // Handle document if medical leave (SKIP file validation for now)
        // We'll process without files to avoid validation errors
        // if (strtolower($leaveType->name) === 'medical' && $scenario['has_document']) {
        //     $requestData['document'] = $this->createFakeDocument();
        // }

        // Create mock Request object
        $request = Request::create('/leave/process', 'POST', $requestData);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Run through your ACTUAL business logic
        $validation = $this->validationService->validateRequest($request);

        if (!$validation['success']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        $conflict = $this->conflictService->checkConflicts($request, $validation);

        if (!$conflict['success']) {
            return ['success' => false, 'message' => $conflict['message']];
        }

        $creditCheck = $this->creditService->checkCredits($request, $validation, $conflict);

        $result = $this->executionService->createLeave($request, $validation, $conflict, $creditCheck);

        Auth::logout();

        return $result;
    }

    protected function generateScenario($leaveType)
    {
        $now = \Carbon\Carbon::now();

        $scenarios = [
            // Good scenarios (likely to be approved)
            [
                'start_date' => $now->copy()->addDays(rand(7, 30)),
                'end_date' => $now->copy()->addDays(rand(7, 30))->addDays(rand(1, 3)),
                'reason' => 'Planned family event',
                'has_document' => rand(0, 100) > 40,
            ],

            // Last-minute scenarios (might be rejected)
            [
                'start_date' => $now->copy()->addHours(rand(12, 23)),
                'end_date' => $now->copy()->addHours(rand(12, 23))->addDays(1),
                'reason' => 'Urgent personal matter',
                'has_document' => rand(0, 100) > 60,
            ],

            // Strategic weekend bridge (likely flagged)
            [
                'start_date' => $now->copy()->next('Friday'),
                'end_date' => $now->copy()->next('Friday'),
                'reason' => 'Personal work',
                'has_document' => false,
            ],

            // Early application (bonus points)
            [
                'start_date' => $now->copy()->addDays(rand(15, 45)),
                'end_date' => $now->copy()->addDays(rand(15, 45))->addDays(rand(2, 5)),
                'reason' => 'Pre-planned vacation',
                'has_document' => rand(0, 100) > 30,
            ],
        ];

        $scenario = $scenarios[array_rand($scenarios)];

        // Ensure end_date is always >= start_date
        $start = Carbon::parse($scenario['start_date']);
        $end = Carbon::parse($scenario['end_date']);

        if ($end->lt($start)) {
            $scenario['end_date'] = $start->copy()->addDays(rand(1, 3));
        }

        // For medical leaves, customize
        if (strtolower($leaveType->name) === 'medical') {
            $scenario['has_document'] = false; // Skip document for now
            $scenario['reason'] = 'Medical treatment required';
        }

        return $scenario;
    }

    protected function createFakeDocument()
    {
        // For simulation, we'll skip actual file creation
        // Just return null - medical leaves will be processed as provisional
        return null;
    }
}