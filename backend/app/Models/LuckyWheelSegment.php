<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LuckyWheelSegment extends Model
{
    protected $fillable = ['name', 'type', 'value', 'color', 'probability_weight', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'probability_weight' => 'decimal:2'];
    }

}
