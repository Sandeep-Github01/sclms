<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveCredit;
use App\Models\Approval;
use App\Models\LeaveType;

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

    // Auto-insert LeaveCredits after user creation
    protected static function booted()
    {
        static::created(function ($user) {
            $leaveTypes = LeaveType::all();

            foreach ($leaveTypes as $type) {
                LeaveCredit::firstOrCreate([
                    'user_id' => $user->id,
                    'type_id' => $type->id,
                ], [
                    'remaining_days' => $type->max_days ?? 0,
                ]);
            }
        });
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveCredits()
    {
        return $this->hasMany(LeaveCredit::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'approved_by');
    }
}
