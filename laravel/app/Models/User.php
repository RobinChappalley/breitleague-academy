<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'username',
        'email',
        'password',
        'pos_id',
        'signup_year',
        'avatar',
        'elo_score',
        'battle_won',
        'battle_lost',
        'number_available_slots',
        'is_BS',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_BS' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function pos()
    {
        //$pos = Pos::find($this->pos_id);
        //$this->pos = $pos;
        //return $this;
        return $this->belongsTo(Pos::class, 'pos_id');
    }



    public function missions()
    {
        return $this->belongsToMany(Mission::class, 'user_missions')
            ->withPivot('is_completed', 'start_date', 'end_date')
            ->withTimestamps();
    }

    //public function userMissions()
    //{
    //    return $this->hasMany(UserMission::class);
    //}

    public function battlesAsChallenger()
    {
        return $this->hasMany(Battle::class, 'challenger_id');
    }

    public function battlesAsChallenged()
    {
        return $this->hasMany(Battle::class, 'challenged_id');
    }

    public function progression()
    {
        return $this->hasOne(Progression::class);
    }

    //public function userRewards()
    //{
    //    return $this->hasMany(UserReward::class);
    //}

    public function rewards()
    {
        return $this->belongsToMany(Reward::class, 'user_rewards')
            ->withPivot(['id', 'is_favourite', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    public function favouriteRewards()
    {
        return $this->hasMany(UserReward::class)->where('is_favourite', true);
    }
}
