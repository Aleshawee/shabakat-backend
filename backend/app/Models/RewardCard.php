<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RewardCard extends Model
{
    use SoftDeletes;

    protected $fillable = ['reward_id', 'code', 'status', 'redeemed_by_user_id', 'redeemed_at'];

    public function reward() { return $this->belongsTo(Reward::class); }
    public function redeemedBy() { return $this->belongsTo(User::class, 'redeemed_by_user_id'); }
}
