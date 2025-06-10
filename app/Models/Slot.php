<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    protected $table = 'slots';
    protected $fillable = [
        'index',
        'module_id',
        'event_id'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
