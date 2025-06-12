<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'theory_id', 'content_default', 'content_if_TF', 'content_if_blank', 
        'isMatchable', 'correct_answer_text', 'position_order'
    ];

    protected $casts = [
        'correct_answer_text' => 'array',
        'isMatchable' => 'boolean'
    ];

    public function theory()
    {
        return $this->belongsTo(Theory::class);
    }

    public function choices()
    {
        return $this->hasMany(Choice::class);
    }
}
