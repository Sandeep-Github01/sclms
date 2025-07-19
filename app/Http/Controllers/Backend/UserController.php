<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'student')
            ->orderBy('name')
            ->get(['id', 'image', 'name', 'email', 'dept_name']);

        $teachers = User::where('role', 'teacher')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'dept_name']);

        return view('backend.user.index', compact('students', 'teachers'));
    }

    public function show($id)
    {
        $user = User::with('department')->findOrFail($id);
        return view('backend.user.show', compact('user'));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User deleted (internally only).');
    }

    public function reviewForm($id)
    {
        $user = User::findOrFail($id);
        return view('backend.user.profile_review', compact('user'));
    }

    public function review(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->input('action') === 'approve') {
            $user->profile_status = 'Approved';
            $user->status = 'Active';
            $user->save();

            // TODO: Send acceptance email to user
            // Mail::to($user->email)->send(new ProfileApprovedMail($user));

            return redirect()->route('admin.user.index')->with('success', 'Profile approved and activated.');
        }

        if ($request->input('action') === 'decline') {
            $reason = $request->input('reason');
            $user->profile_status = 'Declined';
            $user->save();

            // TODO: Send decline email with reason
            // Mail::to($user->email)->send(new ProfileDeclinedMail($user, $reason));

            return redirect()->route('admin.user.index')->with('error', 'Profile declined.');
        }

        return back()->with('error', 'Invalid action.');
    }
}
