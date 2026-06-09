<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsherSetting extends Model
{
    protected $fillable = [
        'is_enabled', 'loan_reward_id', 'point_cost', 'min_points_threshold',
        'starts_at', 'ends_at', 'require_recent_activity', 'activity_days',
        'auto_reset_points', 'last_reset_date',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'require_recent_activity' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'auto_reset_points' => 'boolean',
            'last_reset_date' => 'date',
        ];
    }

    public function loanReward() { return $this->belongsTo(Reward::class, 'loan_reward_id'); }
}
