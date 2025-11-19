<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Import related models
use App\Models\User;
use App\Models\LeaveRequest;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
