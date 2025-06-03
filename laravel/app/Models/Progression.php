<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Progression extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'last_lesson_id',
        'last_checkpoint_id',
        'idofquestionscorrectlyanswered',
    ];

    protected $casts = [
        'idofquestionscorrectlyanswered' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lastLesson()
    {
        return $this->belongsTo(Lesson::class, 'last_lesson_id');
    }

    public function lastCheckpoint()
    {
        return $this->belongsTo(Checkpoint::class, 'last_checkpoint_id');
    }
}
