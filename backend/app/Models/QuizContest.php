<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizContest extends Model
{
    protected $fillable = ['title', 'description', 'entry_fee', 'prize', 'starts_at', 'ends_at', 'status'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function questions() { return $this->hasMany(QuizQuestion::class, 'contest_id'); }
}
