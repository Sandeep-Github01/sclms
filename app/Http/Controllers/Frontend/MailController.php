<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyMail;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class MailController extends Controller
{
    /*
    public function sendVerificationMail($userId)
    {
        $user = User::findOrFail($userId);

        $verificationUrl = URL::temporarySignedRoute('frontend.emails.verify-email', now()->addMinutes(60), ['id' => $user->id]);

        Mail::to($user->email)->send(new VerifyMail($user, $verificationUrl));
        
        return back()->with('success', 'Verification email sent successfully!');
    }
*/
}
