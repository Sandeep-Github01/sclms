<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use App\Models\Admin;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AdminController extends Controller
{
    /**
     * Show the admin login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('backend.admin.login');
    }

    /**
     * Handle an admin login attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route('backend.dashboard');
        }

        return redirect()->back()->withErrors(['error' => 'Invalid credentials.']);
    }

    /**
     * Logout the admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Count all users
        $totalUsers = User::count();

        // Count students
        $studentsCount = User::where('role', 'student')->count();

        // Count teachers
        $teachersCount = User::where('role', 'teacher')->count();

        // Count pending leave requests
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();

        return view('backend.admin.dashboard', compact('totalUsers', 'studentsCount', 'teachersCount', 'pendingLeaves'));
    }

    /**
     * Display the admin profile page.
     *
     * @return \Illuminate\View\View
     */
    public function profileIndex()
    {
        $admin = Auth::guard('admin')->user();

        return view('backend.admin.profile', compact('admin'));
    }
    /**
     * Update the admin profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function profileUpdate(Request $request)
    {
        /** @var \App\Models\Admin $admin */
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
    }

    public function showForgotPasswordForm()
    {
        return view('backend.admin.forgot_password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admins,email',
        ]);

        $status = Password::broker('admins')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm(Request $request, $token)
    {
        $email = $request->query('email');
        return view('backend.admin.reset_password', compact('token', 'email'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admins,email',
            'password' => 'required|confirmed|min:3',
            'token' => 'required'
        ]);

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($admin, $password) {
                $admin->password = Hash::make($password);
                $admin->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')
                ->with('success', 'Password reset successfully. You can now login.');
        } else {
            return back()->withErrors(['email' => __($status)]);
        }
    }
}
