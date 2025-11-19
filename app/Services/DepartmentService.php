<?php

namespace App\Services;

use App\Models\Department;

class DepartmentService
{
    public function create(array $data)
    {
        return Department::create($data);
    }

    public function update(Department $dept, array $data)
    {
        $dept->update($data);
        return $dept;
    }
}
