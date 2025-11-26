<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MedicalDocUploadedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leave;

    public function __construct(LeaveRequest $leave)
    {
        $this->leave = $leave;
    }

    public function build()
    {
        return $this
            ->subject('Medical Document Uploaded - Review Required')
            ->view('emails.leave.medical_doc_uploaded');
    }
}