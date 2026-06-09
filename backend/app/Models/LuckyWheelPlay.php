<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LuckyWheelPlay extends Model
{
    protected $table = 'lucky_wheel_plays';

    protected $fillable = ['user_id', 'lucky_wheel_id', 'prize_id', 'points_spent', 'result'];

    public function user() { return $this->belongsTo(User::class); }
    public function prize() { return $this->belongsTo(LuckyWheelPrize::class); }
    public function luckyWheel() { return $this->belongsTo(LuckyWheel::class); }
}
