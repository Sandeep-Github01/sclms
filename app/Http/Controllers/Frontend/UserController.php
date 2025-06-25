<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use App\Mail\VerifyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Department;

class UserController extends Controller
{
    public function register_show()
    {
        $departments = Department::pluck('name'); 
        return view("frontend.user.register", compact('departments'));
    }

    public function login_show()
    {
        return view("frontend.user.login"); 
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


    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('frontend.user.login')->with('success', 'Logged out successfully.');
    }

public function verify_email(Request $request, $id)
{
    // Signed URL validity जाँच
    if (! $request->hasValidSignature()) {
        abort(401, 'Invalid or expired verification link.');
    }

    $user = User::findOrFail($id);
    if (! $user->email_verified_at) {
        $user->email_verified_at = now();
        $user->save();
    }

    // Verificationपछि login पेजमा success सन्देशसहित redirect
    return redirect()->route('frontend.user.login')
                     ->with('success', 'Email verified successfully. You can now log in.');
}



    public function verificationSent()
{
    return view('frontend.emails.verificationSent'); // ✅ corrected path
}
}
