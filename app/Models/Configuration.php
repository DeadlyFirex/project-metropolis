<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;

    protected $table = 'configurations';
    protected $fillable = [
        'name',
        'description',
        'modules',
        'user_id',
    ];

    public function slots()
    {
        return $this->hasMany(Slot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
