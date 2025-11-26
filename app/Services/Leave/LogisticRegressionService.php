<?php
namespace App\Services\Leave;

use Illuminate\Support\Facades\Log;

class LogisticRegressionService
{
    protected array $coeff;
    protected float $bias;

    public function __construct(array $coeff = [], float $bias = 0.0)
    {
        $this->coeff = $coeff;
        $this->bias = $bias;
    }

    public function predict(array $features): float
    {
        // weighted sum
        $z = $this->bias;
        foreach ($this->coeff as $k => $w) {
            if (!array_key_exists($k, $features)) {
                Log::warning("LR feature missing", ['feature' => $k, 'given' => array_keys($features)]);
            }
            $z += $w * ($features[$k] ?? 0);
        }

        // sigmoid with overflow guard
        $z = max(-500, min(500, $z));
        return 1.0 / (1.0 + exp(-$z));
    }
}