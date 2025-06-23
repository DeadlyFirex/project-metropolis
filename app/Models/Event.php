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
        'start_time',
        'end_time',
        'is_recurring',
        'event_type_id',
        'slot_id',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
    ];


    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    public function type()
    {
        return $this->belongsTo(EventType::class);
    }

    public function effects()
    {
        return $this->hasMany(Effect::class);
    }

    public function slot() {
        return $this->belongsTo(Slot::class);
    }
}

