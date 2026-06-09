<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LuckyWheelPrize extends Model
{
    protected $table = 'lucky_wheel_prizes';

    protected $fillable = ['lucky_wheel_id', 'name', 'type', 'value', 'weight', 'color'];

    protected function casts(): array
    {
        return ['weight' => 'integer'];
    }

    public function luckyWheel() { return $this->belongsTo(LuckyWheel::class); }
}
