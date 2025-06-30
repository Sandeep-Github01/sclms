<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use App\Mail\VerifyMail;
use App\Models\Department;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function login_show()
    {
        return view("frontend.user.login"); 
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->email_verified_at) {
                Auth::login($user);
                return redirect()->route('frontend.user.dashboard');
            }    
            return back()->withErrors(['email' => 'Please verify your email before logging in.']);
        }    
        return back()->withErrors(['email' => 'Invalid credentials.']);
    }
    public function register_show()
    {
        $departments = Department::pluck('name'); 
        return view("frontend.user.register", compact('departments'));
    }
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:3',
            'role' => 'required|string',
            'dept_name' => 'required|string',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->dept_name = $request->dept_name;
        $user->save();

        $verificationUrl = URL::temporarySignedRoute(
            'frontend.emails.verify-email',
            now()->addMinutes(60),
            ['id' => $user->id]
        );
        Mail::to($user->email)->send(new VerifyMail($user, $verificationUrl));

        return redirect()->route('frontend.emails.verificationSent');
    }
    public function verificationSent()
    {
        return view('frontend.emails.verificationSent');
    }    
    public function verify_email(Request $request, $id)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Invalid or expired verification link.');
        }
        
        $user = User::findOrFail($id);
        if (! $user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }
        return redirect()->route('frontend.user.login')->with('success', 'Email verified successfully. You can now log in.');
    }
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('frontend.user.login')->with('success', 'Logged out successfully.');
    }

public function showForgotPasswordForm()
{
    return view('frontend.user.forgot-password');
}

public function sendResetLinkEmail(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $user = User::where('email', $request->email)->first();
    $token = Str::random(60);
    $user->remember_token = $token;
    $user->save();

    $link = route('frontend.user.reset-password', ['token' => $token, 'email' => $user->email]);

    Mail::raw("Click to reset your password:\n\n" . $link, function ($message) use ($user) {
        $message->to($user->email)->subject('Reset Password');
    });

    return back()->with('status', 'Reset link sent to your email!');
}

public function showResetPasswordForm(Request $request, $token)
{
    $email = $request->query('email');
    return view('frontend.user.reset-password', compact('token', 'email'));
}

public function updatePassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'password' => 'required|confirmed|min:3',
        'token' => 'required'
    ]);

    $user = User::where('email', $request->email)
                ->where('remember_token', $request->token)
                ->first();

    if (!$user) {
        return back()->withErrors(['email' => 'Invalid token or email.']);
    }

    $user->password = Hash::make($request->password);
    $user->remember_token = null;
    $user->save();

    return redirect()->route('frontend.user.login')
                     ->with('success', 'Password reset successfully. You can now login.');
}



}
