<?php

namespace App\Services;

use App\Models\Leave;
use Illuminate\Support\Facades\Auth;

class LeaveService
{
    public function getAll()
    {
        return Leave::with(['user', 'department'])->latest()->get();
    }

    public function getById($id)
    {
        return Leave::with(['user', 'department'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['user_id'] = Auth::id();
        return Leave::create($data);
    }

    public function update($id, array $data)
    {
        $leave = Leave::findOrFail($id);
        $leave->update($data);
        return $leave;
    }

    public function delete($id)
    {
        $leave = Leave::findOrFail($id);
        return $leave->delete();
    }

    public function getUserLeaves($userId)
    {
        return Leave::where('user_id', $userId)
            ->with(['department'])
            ->latest()
            ->get();
    }

    public function getDepartmentLeaves($departmentId)
    {
        return Leave::where('department_id', $departmentId)
            ->with(['user'])
            ->latest()
            ->get();
    }
}
