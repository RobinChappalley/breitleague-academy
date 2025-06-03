<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Battle extends Model
{
    use HasFactory;

    protected $table = 'matches'; // important !

    protected $fillable = [
        'challenger_id',
        'challenged_id',
        'has_challenger_won',
        'has_challenger_accepted',
        'has_challenged_accepted',
        'challenger_init_date',
        'questions_id',
        'challenger_summary',
        'challenged_summary',
    ];

    protected $casts = [
        'has_challenger_won' => 'boolean',
        'has_challenger_accepted' => 'boolean',
        'has_challenged_accepted' => 'boolean',
        'challenger_init_date' => 'datetime',
        'questions_id' => 'array',
        'challenger_summary' => 'array',
        'challenged_summary' => 'array',
    ];

    public function challenger()
    {
        return $this->belongsTo(User::class, 'challenger_id');
    }

    public function challenged()
    {
        return $this->belongsTo(User::class, 'challenged_id');
    }
}
