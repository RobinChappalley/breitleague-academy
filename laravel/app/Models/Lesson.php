<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'module_id',
        'order_position',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function theories()
    {
        return $this->hasMany(Theory::class);
    }

    public function checkpoints()
    {
        return $this->hasMany(Checkpoint::class);
    }
}
