<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'order_position',
    ];

    public function lessons()
    {
        return $this->hasMany(Lesson::class); // si tu as une relation de ce type plus tard
    }

    public function checkpoints()
    {
        return $this->hasMany(Checkpoint::class);
    }
}
