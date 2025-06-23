<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlackoutPeriod;
use Illuminate\Support\Carbon;

class BlackoutPeriodSeeder extends Seeder
{
    public function run()
    {
        $periods = [
            [
                'start_date' => '2025-07-01',
                'end_date'   => '2025-07-10',
                'reason'     => 'Semester Final Exams',
            ],
            
        ];

        foreach ($periods as $p) {
            BlackoutPeriod::updateOrCreate(
                [
                    'start_date' => $p['start_date'],
                    'end_date'   => $p['end_date'],
                ],
                ['reason' => $p['reason']]
            );
        }
    }
}
