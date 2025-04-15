<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FunctionModel;

class GridSlot extends Model
{
    use HasFactory;

    protected $table = 'gridslots';

    protected $fillable = [
        'location',
    ];

    public function contains()
    {
        return $this->belongsTo(FunctionModel::class);
    }
}
