<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // List all users grouped by role, department, batch
    public function index()
    {
        // Fetch all students and teachers
        $students = User::where('role', 'student')->with('department')->orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->with('department')->orderBy('name')->get();

        // Grouping by department and batch will be done in blade using collection methods
        return view('backend.user.index', compact('students', 'teachers'));
    }

    // View individual user (read-only)
    public function show($id)
    {
        $user = User::with('department')->findOrFail($id);
        return view('backend.user.show', compact('user'));
    }

    // Destroy method exists but is not exposed
    public function destroy($id)
    {
        // Not used anywhere in UI for now
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User deleted (internally only).');
    }
}
