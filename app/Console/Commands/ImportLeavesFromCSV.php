<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\LeaveType;
use App\Services\Leave\LeaveValidationService;
use App\Services\Leave\LeaveConflictService;
use App\Services\Leave\LeaveCreditService;
use App\Services\Leave\LeaveExecutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImportLeavesFromCSV extends Command
{
    protected $signature = 'leaves:import {file : Path to CSV file}';
    protected $description = 'Import leave requests from CSV and process through full business logic';

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
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("❌ File not found: {$filePath}");
            return 1;
        }

        $this->info("📂 Reading CSV: {$filePath}");

        $csv = array_map('str_getcsv', file($filePath));
        $headers = array_map('trim', array_shift($csv)); // Remove header row

        $this->info("📊 Found " . count($csv) . " records");
        $this->newLine();

        $progressBar = $this->output->createProgressBar(count($csv));
        $progressBar->start();

        $results = [
            'approved' => 0,
            'rejected' => 0,
            'pending' => 0,
            'provisional' => 0,
            'errors' => 0,
        ];

        foreach ($csv as $index => $row) {
            try {
                $data = array_combine($headers, $row);
                $result = $this->processRow($data, $index + 2); // +2 for header and 0-index

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
                $this->error("Row " . ($index + 2) . ": " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->info('✅ Import Complete!');
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

    protected function processRow($data, $rowNumber)
    {
        // Find user by email or ID
        $user = isset($data['user_email'])
            ? User::where('email', trim($data['user_email']))->first()
            : User::find($data['user_id'] ?? null);

        if (!$user) {
            throw new \Exception("User not found");
        }

        // Find leave type
        $leaveType = LeaveType::where('name', trim($data['leave_type']))->first();
        if (!$leaveType) {
            throw new \Exception("Leave type '{$data['leave_type']}' not found");
        }

        // Simulate login
        Auth::login($user);

        // Prepare request data
        $requestData = [
            'type_id' => $leaveType->id,
            'start_date' => trim($data['start_date']),
            'end_date' => trim($data['end_date']),
            'reason' => trim($data['reason'] ?? 'No reason provided'),
        ];

        // Handle document if path provided
        if (!empty($data['document_path']) && Storage::disk('local')->exists($data['document_path'])) {
            $requestData['document'] = $data['document_path'];
        }

        // Create mock Request
        $request = Request::create('/leave/process', 'POST', $requestData);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Run through business logic
        $validation = $this->validationService->validateRequest($request);
        if (!$validation['success']) {
            Auth::logout();
            throw new \Exception($validation['message']);
        }

        $conflict = $this->conflictService->checkConflicts($request, $validation);
        if (!$conflict['success']) {
            Auth::logout();
            throw new \Exception($conflict['message']);
        }

        $creditCheck = $this->creditService->checkCredits($request, $validation, $conflict);
        $result = $this->executionService->createLeave($request, $validation, $conflict, $creditCheck);

        Auth::logout();

        return $result;
    }
}