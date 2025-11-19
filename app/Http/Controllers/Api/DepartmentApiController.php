<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\Request;

class DepartmentApiController extends Controller
{
    protected $service;

    public function __construct(DepartmentService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json(Department::all());
    }

    public function store(Request $request)
    {
        return response()->json($this->service->create($request->all()));
    }

    public function update(Request $request, Department $department)
    {
        return response()->json($this->service->update($department, $request->all()));
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
