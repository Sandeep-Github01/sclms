<?php

namespace App\Services;

use App\Models\BlackoutPeriod;

class BlackoutPeriodService
{
    public function create(array $data)
    {
        return BlackoutPeriod::create($data);
    }

    public function update(BlackoutPeriod $bp, array $data)
    {
        $bp->update($data);
        return $bp;
    }
}
