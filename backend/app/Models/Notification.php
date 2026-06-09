<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['title', 'body', 'image', 'audience', 'target_user_ids', 'status', 'sent_at'];

    protected function casts(): array
    {
        return ['target_user_ids' => 'array', 'sent_at' => 'datetime'];
    }

}
