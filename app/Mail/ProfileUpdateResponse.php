<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProfileUpdateResponse extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $approved;
    public $messageContent;

    public function __construct(User $user, bool $approved, string $messageContent)
    {
        $this->user = $user;
        $this->approved = $approved;
        $this->messageContent = $messageContent;
    }

    public function build()
    {
        return $this->subject($this->approved ? 'Profile Approved' : 'Profile Declined')
            ->view('backend.emails.profile_update_response')
            ->with([
                'user' => $this->user,
                'approved' => $this->approved,
                'messageContent' => $this->messageContent,
            ]);
    }
}