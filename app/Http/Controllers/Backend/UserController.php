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

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::findOrFail($id);
        $user->status = $request->status;
        $user->save();

        return redirect()->route('admin.user.show', $user->id)->with('success', 'User status updated successfully.');
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
            $user->applyPendingChanges();

            $user->profile_status = 'approved';
            $user->status = 'active';
            $user->save();

            $user->clearPendingChanges();

            $profileRequest->status = 'approved';
            $profileRequest->admin_comment = $request->input('admin_comment') ?? 'Profile approved by admin.';
            $profileRequest->save();

            Mail::to($user->email)->send(new ProfileUpdateResponse($user, true, 'Your changes have been approved.'));

            return redirect()->route('admin.user.review_index')->with('success', 'Profile approved and user notified.');
        }

        if ($request->input('action') === 'decline') {
            $reason = $request->input('reason');
            $adminComment = $request->input('admin_comment');

            $user->clearPendingChanges();

            $user->profile_status = 'declined';
            $user->save();

            $profileRequest->status = 'declined';
            $profileRequest->admin_comment = $adminComment ?? $reason ?? 'Profile declined by admin.';
            $profileRequest->save();

            Mail::to($user->email)->send(new ProfileUpdateResponse($user, false, $reason ?? 'Your changes were rejected.'));

            return redirect()->route('admin.user.review_index')->with('error', 'Profile rejected and user notified.');
        }

        return back()->with('error', 'Invalid action.');
    }
}
