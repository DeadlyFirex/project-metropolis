<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    protected $fillable = ['category', 'max', 'incompatible'];

    protected $casts = [
        'incompatible' => 'array',   // ↔ JSON ↔ PHP-array
        'max'          => 'integer',
    ];
}
