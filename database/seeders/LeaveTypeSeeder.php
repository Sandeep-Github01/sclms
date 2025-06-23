<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            [
                'name' => 'Casual',
                'max_days' => 5,
                'requires_documentation' => false,
            ],
            [
                'name' => 'Medical',
                'max_days' => 14,
                'requires_documentation' => true,
            ],
            [
                'name' => 'Emergency',
                'max_days' => 7,
                'requires_documentation' => true,
            ],
            [
                'name' => 'Academic',
                'max_days' => 3,
                'requires_documentation' => false,
            ],
        ];

        foreach ($types as $t) {
            LeaveType::updateOrCreate(
                ['name' => $t['name']],
                [
                    'max_days' => $t['max_days'],
                    'requires_documentation' => $t['requires_documentation'],
                ]
            );
        }
    }
}
