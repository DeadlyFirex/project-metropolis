<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserClock extends Model
{
    protected $fillable = ['user_id', 'clock_time'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
