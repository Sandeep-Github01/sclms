<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlackoutPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date',
        'end_date',
        'reason',
        'department_id',
        'semester'
    ];
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
