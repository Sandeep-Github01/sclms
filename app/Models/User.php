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
        'image',
        'dept_name',
        'role',
        'dob',
        'phone',
        'address',
        'gender',
        'status',
        'batch',
        'semester',
        'is_profile_complete',
        'profile_status',
        'last_login_at',
    ];

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

    public function profileUpdateRequests()
    {
        return $this->hasMany(ProfileUpdateRequest::class);
    }

    public function latestPendingUpdate()
    {
        return $this->profileUpdateRequests()->where('status', 'pending')->latest()->first();
    }

    public function isProfileIncomplete()
    {
        if (!$this->is_profile_complete || $this->profile_status !== 'approved') {
            return true;
        }

        $requiredFields = [
            'name',
            'email',
            'image',
            'dept_name',
            'role',
            'dob',
            'phone',
            'address',
            'gender',
            'status'
        ];

        foreach ($requiredFields as $field) {
            if (empty($this->{$field})) {
                return true;
            }
        }

        if ($this->role === 'student') {
            if (empty($this->batch) || empty($this->semester)) {
                return true;
            }
        }

        return false;
    }

    public function applyPendingChanges()
    {
        $request = $this->latestPendingUpdate();

        if ($request && $request->data) {
            foreach ($request->data as $field => $value) {
                if (in_array($field, $this->fillable)) {
                    $this->{$field} = $value;
                }
            }
        }
    }

    public function clearPendingChanges()
    {
        // Reserved for future cleanup logic
    }
}
