<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'tenant_id', 'permissions', 'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function tenant(): ?\App\Models\Tenant
    {
        if (!tenancy()->initialized) {
            return null;
        }
        return tenancy()->tenant;
    }

    public function isNetworkAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
