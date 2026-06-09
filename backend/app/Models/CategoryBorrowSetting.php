<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryBorrowSetting extends Model
{
    protected $fillable = ['category_id', 'is_borrowable', 'max_borrow_amount', 'min_points_threshold'];

    protected function casts(): array
    {
        return ['is_borrowable' => 'boolean'];
    }

    public function category() { return $this->belongsTo(Category::class); }
}
