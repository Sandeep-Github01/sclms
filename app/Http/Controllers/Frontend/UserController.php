<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\VerifyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class UserController extends Controller
{
    public function register_show()
    {
        return view("frontend.user.register"); 
    }

    public function login_show()
    {
        return view("frontend.user.login"); 
    }

   public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email', // ✅ corrected table name
        'password' => 'required|confirmed|min:3',
        'role' => 'required|string',
        'department_id' => 'required|integer',
    ]);

    $user = new User();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = Hash::make($request->password);
    $user->role = $request->role;
    $user->department_id = $request->department_id;
    $user->save();

    Mail::to($user->email)->send(new VerifyMail($user));

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
            return redirect()->route('dashboard');
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
    $user = User::findOrFail($id);

    if (!$user->email_verified_at) {
        $user->email_verified_at = now();
        $user->save();
    }

    return redirect()->route('frontend.user.login')->with('message', 'Email verified successfully');
}


    public function verificationSent()
{
    return view('frontend.emails.verificationSent'); // ✅ corrected path
}
}
