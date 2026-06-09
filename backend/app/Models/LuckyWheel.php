<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LuckyWheel extends Model
{
    protected $table = 'lucky_wheels';

    protected $fillable = ['name', 'spin_mode', 'point_cost', 'daily_limit', 'color', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'point_cost' => 'integer', 'daily_limit' => 'integer'];
    }

    public function prizes() { return $this->hasMany(LuckyWheelPrize::class, 'lucky_wheel_id'); }
}
