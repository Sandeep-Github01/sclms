<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\ProfileUpdateResponse;
use App\Http\Controllers\Controller;
use App\Models\ProfileUpdateRequest;
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

    public function reviewIndex()
    {
        $pendingRequests = ProfileUpdateRequest::with('user')
            ->where('status', 'pending')
            ->get();

        return view('backend.user.review_index', compact('pendingRequests'));
    }

    public function profileReviewForm($id)
    {
        $user = User::findOrFail($id);
        $profileRequest = $user->latestPendingUpdate();

        if (!$profileRequest) {
            return redirect()->route('admin.user.review_index')->with('error', 'No pending profile update found for this user.');
        }

        $proposed = $profileRequest->data;

        return view('backend.user.profile_review', [
            'user' => $user,
            'profileRequest' => $profileRequest,
            'proposed' => $proposed,
        ]);
    }

    public function processProfileReview(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $profileRequest = $user->latestPendingUpdate();

        if (!$profileRequest) {
            return redirect()->route('admin.user.review_index')->with('error', 'No pending profile update found for this user.');
        }

        if ($request->input('action') === 'approve') {
            // Apply the pending changes to user object
            $user->applyPendingChanges();

            // Update user status and profile_status
            $user->profile_status = 'Approved';
            $user->status = 'Active';
            $user->save(); // Save the user with applied changes

            // Clear any pending data (cleanup)
            $user->clearPendingChanges();

            // Update the ProfileUpdateRequest status to approved
            $profileRequest->status = 'approved';
            $profileRequest->save();

            // Send approved mail
            Mail::to($user->email)->send(new ProfileUpdateResponse($user, true, 'Your changes have been approved.'));

            return redirect()->route('admin.user.review_index')->with('success', 'Profile approved and user notified.');
        }

        if ($request->input('action') === 'decline') {
            $reason = $request->input('reason');

            // Clear any pending data (cleanup)
            $user->clearPendingChanges();

            // Update user profile_status
            $user->profile_status = 'Declined';
            $user->save();

            // Update the ProfileUpdateRequest status to declined
            $profileRequest->status = 'declined';
            $profileRequest->save();

            // Send rejection mail
            Mail::to($user->email)->send(new ProfileUpdateResponse($user, false, $reason ?? 'Your changes were rejected.'));

            return redirect()->route('admin.user.review_index')->with('error', 'Profile rejected and user notified.');
        }

        return back()->with('error', 'Invalid action.');
    }
}
