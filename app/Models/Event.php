<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';
    protected $fillable = [
        'name',
        'description',
        'image_path',
        'duration',
        'event_type_id',
    ];

    public function type()
    {
        return $this->hasOne(EventType::class);
    }

    public function effects()
    {
        return $this->hasMany(Effect::class);
    }
}

