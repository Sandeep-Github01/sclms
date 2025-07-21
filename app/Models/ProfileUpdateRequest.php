<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileUpdateRequest extends Model
{
    protected $fillable = ['user_id', 'data', 'status', 'admin_comment'];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
