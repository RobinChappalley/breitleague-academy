<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReward extends Model
{
    protected $fillable = [
        'user_id',
        'reward_id',
        'is_favourite',
        'acquired_at',
    ];
    protected $casts = [
        'is_favourite' => 'boolean',
        'acquired_at' => 'datetime',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }
}
