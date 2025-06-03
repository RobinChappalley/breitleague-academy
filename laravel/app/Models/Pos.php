<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pos extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'zipcode',
        'country',
        'breitling_pin',
        'country_flag',
    ];

    protected $casts = [
        'breitling_pin' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'POS_id');
    }
}
