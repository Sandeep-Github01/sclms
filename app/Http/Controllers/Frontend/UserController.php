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
use Illuminate\Support\Facades\Password;

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

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
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

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('frontend.user.login')
                ->with('success', 'Password reset successfully. You can now login.');
        } else {
            return back()->withErrors(['email' => __($status)]);
        }
    }
    public function profile()
    {
        $user = Auth::user();

        return view('frontend.user.profile', compact('user'));
    }
}
