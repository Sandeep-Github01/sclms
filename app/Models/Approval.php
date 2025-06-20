<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'approved_by',
        'status',
        'comment',
    ];

    public function leaveRequest() {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
