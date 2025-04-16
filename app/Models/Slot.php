<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    protected $table = 'slots';
    protected $fillable = [
        'row',
        'column',
        'module_id',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
