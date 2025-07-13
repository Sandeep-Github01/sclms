<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlackoutPeriod;

class BlackoutPeriodController extends Controller
{
    public function index()
    {
        $blackouts = BlackoutPeriod::all();
        return view('backend.AdminWorks.blackout.index', compact('blackouts'));
    }

    public function create()
    {
        return view('backend.AdminWorks.blackout.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        BlackoutPeriod::create($validated);

        return redirect()->route('admin.blackout.index')
            ->with('success', 'Blackout period created successfully.');
    }

    public function edit($id)
    {
        $blackout = BlackoutPeriod::findOrFail($id);
        return view('backend.AdminWorks.blackout.edit', compact('blackout'));
    }

    public function update(Request $request, $id)
    {
        $blackout = BlackoutPeriod::findOrFail($id);

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        $blackout->update($validated);

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
