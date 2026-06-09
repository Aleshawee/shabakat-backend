<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NetworkCard extends Model
{
    use SoftDeletes;

    protected $fillable = ['category_id', 'code', 'status', 'used_by_user_id', 'used_at', 'batch_id'];

    public function category() { return $this->belongsTo(Category::class); }
    public function usedBy() { return $this->belongsTo(User::class, 'used_by_user_id'); }
}
