<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MedicalAbuseDetectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public LeaveRequest $leave;

    public function __construct(LeaveRequest $leave)
    {
        $this->leave = $leave;
    }

    public function build()
    {
        return $this->subject('⚠️ Medical Leave Abuse Detected')
            ->view('emails.medical_abuse_detected')
            ->with([
                'leave' => $this->leave,
                'user'  => $this->leave->user,
            ]);
    }
}
