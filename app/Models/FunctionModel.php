<?php

namespace App\Models;
use App\Models\GridSlot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunctionModel extends Model
{
    use HasFactory;

    protected $table = 'functions';

    protected $fillable = [
        'name',
        'category',
        'image',
    ];

    public function GridSlots()
    {
        return $this->hasMany(GridSlot::class);
    }
}

