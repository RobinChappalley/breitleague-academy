<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_default',
        'content_if_TF',
        'content_if_blank',
        'isMatchable',
        'checkpoint_id',
        'theory_id',
        'position_order',
        'correct_answer_text',
    ];

    protected $casts = [
        'isMatchable' => 'boolean',
        'correct_answer_text' => 'array',
    ];

    public function theory()
    {
        return $this->belongsTo(Theory::class);
    }

    public function checkpoint()
    {
        return $this->belongsTo(Checkpoint::class);
    }

    public function choices()
    {
        return $this->hasMany(Choice::class);
    }
}
