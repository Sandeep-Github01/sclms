<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class LeaveApiController extends Controller
{
    protected $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function index()
    {
        return response()->json($this->leaveService->getAll());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'leave_type'    => 'required|string',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'nullable|string',
        ]);

        return response()->json($this->leaveService->create($validated), 201);
    }

    public function show($id)
    {
        return response()->json($this->leaveService->getById($id));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'leave_type'    => 'nullable|string',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'reason'        => 'nullable|string',
        ]);

        return response()->json($this->leaveService->update($id, $validated));
    }

    public function destroy($id)
    {
        $this->leaveService->delete($id);
        return response()->json(['message' => 'Leave deleted successfully']);
    }

    public function userLeaves($userId)
    {
        return response()->json($this->leaveService->getUserLeaves($userId));
    }

    public function departmentLeaves($departmentId)
    {
        return response()->json($this->leaveService->getDepartmentLeaves($departmentId));
    }
}
