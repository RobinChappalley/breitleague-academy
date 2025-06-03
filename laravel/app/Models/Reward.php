<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    protected $fillable = [
        'photo_name',
        'model',
        'date',
        'size',
        'colors',
        'bracelet',
        'description',
    ];

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class);
    }

    public function userRewards(): HasMany
    {
        return $this->hasMany(UserReward::class);
    }
}
