<?php
namespace App\Services\Leave;

class LeaveEvaluationService
{
    public function evaluate($leave)
    {
        return ['score' => 0, 'probability' => 0, 'steps' => []];
    }
}