<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Effect extends Model
{
    use HasFactory;

    protected $table = 'effects';
    protected $fillable = [
        'type',
        'value',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}

