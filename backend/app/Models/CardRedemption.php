<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardRedemption extends Model
{
    protected $fillable = ['user_id', 'reward_card_id', 'reward_id', 'points_spent'];

    public function user() { return $this->belongsTo(User::class); }
    public function rewardCard() { return $this->belongsTo(RewardCard::class); }
    public function reward() { return $this->belongsTo(Reward::class); }
}
