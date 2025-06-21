<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Show user profile (no edit)
    public function profile()
    {
        $user = Auth::user();
        return view('frontend.user.profile', compact('user'));
    }

    // Show registration form
    public function create()
    {
        return view('frontend.user.register');
    }

    // Store new user and send email verification (placeholder)
    public function store(Request $request)
    {
        // Validate input
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:student,teacher',
        ]);

        // Create user (default: email not verified)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'department_id' => $request->department_id,
        ]);

        // TODO: Send email verification link with token

        return redirect()->route('login')->with('success', 'Please verify your email to log in.');
    }

    // Handle email verification (placeholder)
    public function verifyEmail($token)
    {
        // TODO: Match token and verify email

        return redirect()->route('login')->with('success', 'Email verified. You can now log in.');
    }

    // Show forgot password form
    public function resetRequest()
    {
        return view('frontend.user.forgot-password');
    }

    // Handle reset request (email send placeholder)
    public function resetSubmit(Request $request)
    {
        // TODO: Validate and send password reset email
        return back()->with('status', 'Reset link sent to your email.');
    }
}
