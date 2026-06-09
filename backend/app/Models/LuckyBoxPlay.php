<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LuckyBoxPlay extends Model
{
    protected $fillable = ['user_id', 'lucky_box_id', 'prize_id', 'points_spent', 'result'];

    public function user() { return $this->belongsTo(User::class); }
    public function prize() { return $this->belongsTo(LuckyBoxPrize::class); }
}
