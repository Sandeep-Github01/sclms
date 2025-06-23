<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            'CSIT',
            'BBM',
            'BIT',
            'BBA',
            'MBA',
        ];

        foreach ($departments as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
}
