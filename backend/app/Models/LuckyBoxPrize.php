<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LuckyBoxPrize extends Model
{
    protected $fillable = ['lucky_box_id', 'name', 'type', 'value', 'weight'];

    protected function casts(): array
    {
        return ['weight' => 'integer'];
    }

    public function luckyBox() { return $this->belongsTo(LuckyBox::class); }
}
