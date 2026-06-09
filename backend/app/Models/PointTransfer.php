<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransfer extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'amount', 'fee', 'gross_amount', 'status'];

    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver() { return $this->belongsTo(User::class, 'receiver_id'); }
}
