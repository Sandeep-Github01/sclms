<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveCredit;

class LeaveCreditSeeder extends Seeder
{
    public function run()
    {
        // Default remaining_days per type:
        $defaults = [
            'Casual'    => 5,
            'Medical'   => 14,
            'Emergency' => 7,
            'Academic'  => 3,
        ];

        $types = LeaveType::all()->keyBy('name');

        $users = User::all();
        foreach ($users as $user) {
            foreach ($defaults as $typeName => $days) {
                if (!isset($types[$typeName])) {
                    continue;
                }
                $type = $types[$typeName];
                LeaveCredit::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'type_id' => $type->id,
                    ],
                    ['remaining_days' => $days]
                );
            }
        }
    }
}
