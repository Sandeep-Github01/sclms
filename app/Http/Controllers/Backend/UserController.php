<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\ProfileUpdateResponse;
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

    public function profileReviewForm($id)
    {
        $user = User::findOrFail($id);
        $proposed = $user->pendingChanges(); // You need to implement this in the model
        return view('backend.user.profile_review', compact('user', 'proposed'));
    }

    public function processProfileReview(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->input('action') === 'approve') {
            // Apply the pending changes
            $user->applyPendingChanges(); // Implement in User model
            $user->clearPendingChanges(); // Implement in User model
            $user->profile_status = 'Approved';
            $user->status = 'Active';
            $user->save();

            // Send approved mail
            Mail::to($user->email)->send(new ProfileUpdateResponse($user, true, 'Your changes have been approved.'));

            return redirect()->route('admin.user.index')->with('success', 'Profile approved and user notified.');
        }

        if ($request->input('action') === 'decline') {
            $reason = $request->input('reason');
            $user->clearPendingChanges(); // Implement in User model
            $user->profile_status = 'Declined';
            $user->save();

            // Send rejection mail
            Mail::to($user->email)->send(new ProfileUpdateResponse($user, false, $reason ?? 'Your changes were rejected.'));

            return redirect()->route('admin.user.index')->with('error', 'Profile rejected and user notified.');
        }

        return back()->with('error', 'Invalid action.');
    }
}
