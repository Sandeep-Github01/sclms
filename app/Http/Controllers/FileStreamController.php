<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FileStreamController extends Controller
{
    public function leaveDoc(LeaveRequest $leave)
    {
        try {
            $user = Auth::user();
            abort_if(empty($leave->file_path), 404, 'No document found');

            // Authorize: owner OR admin
            if (
                !Auth::guard('admin')->check() &&
                (!$user || $user->id !== $leave->user_id)
            ) {
                abort(403, 'Unauthorized access to document');
            }

            // Add 'private/' prefix if it doesn't exist
            $path = $leave->file_path;
            if (!str_starts_with($path, 'private/')) {
                $path = 'private/' . $path;
            }

            Log::info('Looking for file at: ' . $path);

            $fullPath = storage_path('app/' . $path);

            if (!file_exists($fullPath)) {
                Log::error('File not found: ' . $fullPath);
                abort(404, 'Document file not found on server');
            }

            if (!is_readable($fullPath)) {
                Log::error('File not readable: ' . $fullPath);
                abort(500, 'Document file cannot be read');
            }

            Log::info('Serving file: ' . $fullPath);

            // Serve the file
            return response()->file($fullPath);

        } catch (\Exception $e) {
            Log::error('Error serving document: ' . $e->getMessage());
            abort(500, 'Error loading document: ' . $e->getMessage());
        }
    }
}