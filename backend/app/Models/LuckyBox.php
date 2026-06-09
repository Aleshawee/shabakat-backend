<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LuckyBox extends Model
{
    protected $fillable = ['name', 'cost', 'daily_limit', 'color', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'cost' => 'integer', 'daily_limit' => 'integer'];
    }

    public function prizes() { return $this->hasMany(LuckyBoxPrize::class, 'lucky_box_id'); }
}
