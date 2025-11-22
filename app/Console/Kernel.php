<?php

namespace App\Console;

use App\Mail\MedicalAbuseDetectedMail;
use App\Mail\MedicalAbuseRejectedMail;
use App\Models\Admin;
use App\Models\LeaveRequest;
use App\Services\Leave\PenaltyService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * Define your scheduled tasks.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run daily at 00:10 AM
        $schedule->call(function () {

            $now = Carbon::now();

            // All provisional leaves whose deadline passed AND doc not uploaded
            $leaves = LeaveRequest::with(['user', 'leaveType'])
                ->where('status', 'provisional')
                ->whereNotNull('document_deadline')
                ->where('document_deadline', '<', $now)
                ->where('document_status', 'pending')
                ->get();

            if ($leaves->isEmpty()) {
                return;
            }

            $penaltyService = app(PenaltyService::class);
            $admins = Admin::all();

            foreach ($leaves as $leave) {

                // mark abuse
                $penaltyService->markAbuse(
                    $leave,
                    'medical_provisional_abuse',
                    null,
                    'system',
                    null
                );


                // update doc status
                $leave->document_status = 'missing';
                $leave->save();

                // notify all admins
                if ($admins->isNotEmpty()) {
                    foreach ($admins as $admin) {
                        Mail::to($admin->email)->send(
                            new MedicalAbuseDetectedMail($leave)
                        );
                    }
                }

                // notify user
                Mail::to($leave->user->email)->send(
                    new MedicalAbuseRejectedMail($leave)
                );
            }
        })->dailyAt('00:10');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
