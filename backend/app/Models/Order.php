<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'total', 'status', 'payment_method', 'payment_instructions', 'payment_receipt', 'paid_at'];

    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
}
