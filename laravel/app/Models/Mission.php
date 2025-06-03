<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'reward_title',
        'reward_description',
        'reward_image_url',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_missions')
            ->withPivot('is_completed', 'start_date', 'end_date')
            ->withTimestamps();
    }

    public function userMissions()
    {
        return $this->hasMany(UserMission::class);
    }
}
