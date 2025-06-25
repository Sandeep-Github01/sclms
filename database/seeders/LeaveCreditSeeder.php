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
        $types = LeaveType::all();

        $users = User::all();

        foreach ($users as $user) {
            foreach ($types as $type) {
                $days = $type->max_days ?? 0;

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
