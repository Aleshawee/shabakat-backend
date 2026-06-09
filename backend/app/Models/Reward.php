<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = ['category_id', 'name', 'description', 'points_cost', 'card_value', 'image', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'card_value' => 'decimal:2'];
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function cards() { return $this->hasMany(RewardCard::class); }
}
