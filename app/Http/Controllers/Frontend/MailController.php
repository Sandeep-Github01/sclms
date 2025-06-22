<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyMail;
use App\Models\User;

class MailController extends Controller
{
    public function sendVerificationMail($userId)
    {
        $user = User::findOrFail($userId);
        Mail::to($user->email)->send(new VerifyMail($user));
        return back()->with('success', 'Verification email sent successfully!');
    }
}
