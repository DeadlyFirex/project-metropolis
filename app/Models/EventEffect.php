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
        'is_primary_effect',
        'is_adjacent_effect',
    ];

    protected $casts = [
        'is_primary_effect' => 'boolean',
        'is_adjacent_effect' => 'boolean',
    ];

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }
}
