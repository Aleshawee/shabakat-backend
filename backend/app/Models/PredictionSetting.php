<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PredictionSetting extends Model
{
    protected $fillable = ['is_enabled', 'max_active_events', 'min_time_before_deadline', 'allow_prediction_edit', 'edit_fee', 'auto_distribute_rewards'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'allow_prediction_edit' => 'boolean',
            'auto_distribute_rewards' => 'boolean',
        ];
    }

}
