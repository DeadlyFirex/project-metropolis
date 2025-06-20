<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clock extends Model
{
    protected $fillable = [
        'user_id',
        'time',
        'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
