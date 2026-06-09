<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SportPrediction extends Model
{
    protected $fillable = ['event_id', 'user_id', 'prediction', 'points_bet', 'is_winner'];

    protected function casts(): array
    {
        return [
            'is_winner' => 'boolean',
        ];
    }

    public function event() { return $this->belongsTo(SportEvent::class); }
    public function user() { return $this->belongsTo(User::class); }
}
