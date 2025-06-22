<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Import related models
use App\Models\LeaveRequest;
use App\Models\LeaveCredit;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'max_days', 'requires_documentation'];

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'type_id');
    }

    public function leaveCredits()
    {
        return $this->hasMany(LeaveCredit::class, 'type_id');
    }
}
