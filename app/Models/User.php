<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $table      = 'users';
    protected $primaryKey = 'id_user';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'id_user',
        'nama_user',
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ============================================================
    // HELPER METHODS
    // ============================================================

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    public function getDashboardRoute(): string
    {
        return match ($this->role) {
            'admin' => 'dashboard',
            'kasir' => 'dashboard_kasir',
            default => 'login',
        };
    }
}