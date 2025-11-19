<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\LeaveRequest;

class LeaveDecisionMail extends Mailable
{
    public $leave;

    public function __construct(LeaveRequest $leave)
    {
        $this->leave = $leave;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Leave Request Decision'
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leave.decision',
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
