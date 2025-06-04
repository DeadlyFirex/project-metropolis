<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    use HasFactory;

    protected $table = 'event_types';
    protected $fillable = [
        'name',
        'description',
        'module_id',
        'min_duration',
        'max_duration',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function compatible()
    {
        return $this->hasOne(Module::class);
    }

    public function effects()
    {
        return $this->hasMany(EventEffect::class);
    }
}

