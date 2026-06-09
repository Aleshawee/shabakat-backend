<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Network extends Model
{
    protected $fillable = [
        'name', 'slug', 'domain', 'owner_name', 'phone', 'email',
        'commission_rate', 'logo', 'theme_colors', 'status',
    ];

    protected function casts(): array
    {
        return [
            'theme_colors' => 'array',
            'commission_rate' => 'decimal:2',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class, 'network_id');
    }

    public function admins()
    {
        return $this->hasMany(Admin::class, 'network_id');
    }
}
