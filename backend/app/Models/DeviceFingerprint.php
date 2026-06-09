<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceFingerprint extends Model
{
    protected $fillable = ['user_id', 'device_id', 'device_name', 'ip_address', 'risk_level'];

    public function user() { return $this->belongsTo(User::class); }
}
