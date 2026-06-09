<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $fillable = ['contest_id', 'question', 'options', 'correct_answer', 'points', 'sort_order'];

    protected function casts(): array
    {
        return ['options' => 'array'];
    }

    public function contest() { return $this->belongsTo(QuizContest::class); }
    public function answers() { return $this->hasMany(QuizAnswer::class, 'question_id'); }
}
