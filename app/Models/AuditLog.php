<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Import related models
use App\Models\User;
use App\Models\LeaveRequest;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_request_id',
        'action',
        'created_at',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }
}
