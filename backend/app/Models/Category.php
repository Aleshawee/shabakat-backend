<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'price', 'points', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'price' => 'decimal:2'];
    }

    public function networkCards() { return $this->hasMany(NetworkCard::class); }
    public function rewards() { return $this->hasMany(Reward::class); }
    public function borrowSetting() { return $this->hasOne(CategoryBorrowSetting::class, 'category_id'); }
}
