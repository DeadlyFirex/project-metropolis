<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventEffect extends Model
{
    use HasFactory;

    protected $table = 'event_effects';
    protected $fillable = [
        'type',
        'value',
        'event_type_id',
    ];

    public function event_type()
    {
        return $this->belongsTo(EventType::class);
    }
}

