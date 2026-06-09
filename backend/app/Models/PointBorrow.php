<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointBorrow extends Model
{
    protected $fillable = ['user_id', 'amount', 'fee', 'total_debt', 'repaid_amount', 'status', 'repaid_at'];

    public function user() { return $this->belongsTo(User::class); }
}
