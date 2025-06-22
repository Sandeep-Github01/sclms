<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

// Import gareko related models for relationships
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveCredit;
use App\Models\Approval;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
    ];

    // Department sanga relation
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // LeaveRequest sanga relation
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // LeaveCredit sanga relation
    public function leaveCredits()
    {
        return $this->hasMany(LeaveCredit::class);
    }

    // Approval sanga relation (approved_by field)
    public function approvals()
    {
        return $this->hasMany(Approval::class, 'approved_by');
    }
}
