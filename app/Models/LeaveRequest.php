<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
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
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function leaveType() {
        return $this->belongsTo(LeaveType::class, 'type_id');
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function approval() {
        return $this->hasOne(Approval::class);
    }
}
