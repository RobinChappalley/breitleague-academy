<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Choice extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'text_answer',
        'img_answer',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
