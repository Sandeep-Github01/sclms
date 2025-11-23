<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlackoutPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'semester',
        'start_date',
        'end_date',
        'reason',
    ];

    protected $casts = [
        'department_id' => 'array',
        'semester' => 'array',
    ];
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
