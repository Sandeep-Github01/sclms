<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileStreamController extends Controller
{
    /**
     * Stream leave document from private disk.
     * URL: /leave/document/{leave}
     */
    public function leaveDoc(LeaveRequest $leave): StreamedResponse
    {
        $user = Auth::user();               // nullable
        abort_if(empty($leave->file_path), 404);

        // Authorise: owner OR admin
        if (
            !Auth::guard('admin')->check() && // admin gate
            (!$user || $user->id !== $leave->user_id) // null-safe owner check
        ) {
            abort(403, 'Unauthorized access to document');
        }

        $path = $leave->file_path;               // e.g. "private/leave_docs/abc123.pdf"
        $disk = Storage::disk('local');          // same disk you used in store
        abort_if(!$disk->exists($path), 404);

        // Sanitized download name
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $leave->user->name)
            . '-leave.' . pathinfo($path, PATHINFO_EXTENSION);

        // Stream with 1-hour private cache
        return response()->stream(function () use ($disk, $path) {
            fpassthru($disk->readStream($path));
        }, 200, [
            'Content-Disposition' => 'inline; filename="' . $safeName . '"',
            'Cache-Control'       => 'private, max-age=3600',
        ]);
    }
}
