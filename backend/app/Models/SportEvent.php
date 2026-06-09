<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SportEvent extends Model
{
    protected $fillable = ['title', 'description', 'event_type', 'prediction_type', 'allow_draw', 'home_team', 'away_team', 'option_a_image', 'option_b_image', 'match_date', 'prediction_deadline', 'entry_fee', 'reward_per_winner', 'status', 'winner', 'home_score', 'away_score', 'rewards_distributed', 'closed_at'];

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'prediction_deadline' => 'datetime',
            'closed_at' => 'datetime',
            'allow_draw' => 'boolean',
        ];
    }

    public function predictions() { return $this->hasMany(SportPrediction::class, 'event_id'); }
}
