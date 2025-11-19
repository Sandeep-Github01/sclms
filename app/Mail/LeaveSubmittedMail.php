<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\LeaveRequest;

class LeaveSubmittedMail extends Mailable
{
    public $leave;

    public function __construct(LeaveRequest $leave)
    {
        $this->leave = $leave;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Leave Request Submitted'
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leave.submitted',
            with: [
                'leave' => $this->leave,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
