<?php

namespace App\Http\Controllers\Backend;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\BlackoutPeriod;
use App\Http\Controllers\Controller;

class BlackoutPeriodController extends Controller
{
    public function index()
    {
        $blackouts = BlackoutPeriod::all();
        return view('backend.AdminWorks.blackout.index', compact('blackouts'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('backend.AdminWorks.blackout.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
            'department_id' => 'nullable|array',
            'department_id.*' => 'exists:departments,id',
            'semester' => 'nullable|array',
            'semester.*' => 'string|max:20', 
        ]);

        BlackoutPeriod::create([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'] ?? null,
            'department_id' => $validated['department_id'] ?? [],
            'semester' => $validated['semester'] ?? [],
        ]);

        return redirect()->route('admin.blackout.index')
            ->with('success', 'Blackout period created successfully.');
    }

    public function edit($id)
    {
        $blackout = BlackoutPeriod::findOrFail($id);
        $departments = Department::all();
        return view('backend.AdminWorks.blackout.edit', compact('blackout', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $blackout = BlackoutPeriod::findOrFail($id);

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
            'department_id' => 'nullable|array',
            'department_id.*' => 'exists:departments,id',
            'semester' => 'nullable|array',
            'semester.*' => 'string|max:20',
        ]);

        $blackout->update([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'] ?? null,
            'department_id' => $validated['department_id'] ?? [],
            'semester' => $validated['semester'] ?? [],
        ]);

        return redirect()->route('admin.blackout.index')
            ->with('success', 'Blackout period updated successfully.');
    }

    public function destroy($id)
    {
        $blackout = BlackoutPeriod::findOrFail($id);
        $blackout->delete();

        return redirect()->route('admin.blackout.index')
            ->with('success', 'Blackout period deleted successfully.');
    }
}
