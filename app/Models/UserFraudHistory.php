<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFraudHistory extends Model
{
    use HasFactory;

    protected $table = 'user_fraud_histories';

    protected $fillable = [
        'user_id',
        'score',
        'reasons',
        'expires_at',
    ];

    protected $casts = [
        'reasons' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
