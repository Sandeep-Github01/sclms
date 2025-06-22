<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Import related models
use App\Models\User;
use App\Models\LeaveType;

class LeaveCredit extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'type_id', 'remaining_days'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'type_id');
    }
}
