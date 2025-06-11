<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $table = 'modules';
    protected $fillable = [
        'name',
        'description',
        'category',
    ];

    public function configurations()
    {
        return $this->hasMany(Configuration::class);
    }

    public function slot()
    {
        return $this->hasOne(Slot::class);
    }

    public function effects()
    {
        return $this->hasMany(Effect::class);
    }

    public function compatible() {
        return $this->hasMany(EventType::class);
    }
}

