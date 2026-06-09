<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    protected $fillable = ['question_id', 'user_id', 'answer', 'is_correct', 'points_earned'];

    public function question() { return $this->belongsTo(QuizQuestion::class); }
    public function user() { return $this->belongsTo(User::class); }
}
