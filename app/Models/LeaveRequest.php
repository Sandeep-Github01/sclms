<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Import related models
use App\Models\User;
use App\Models\LeaveType;
use App\Models\Department;
use App\Models\Approval;
use App\Models\AuditLog;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type_id',
        'department_id',
        'start_date',
        'end_date',
        'reason',
        'file_path',
        'status',
        'review_type',
        'final_score',
        'status_note',
        'fraud_score',    
        'risk_score',     
        'probability',    
        'notes',          
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'type_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approval()
    {
        return $this->hasOne(Approval::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
