<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';
    protected $fillable = [
        'id_user','nama_user','username', 'password', 'role', 
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $primaryKey = 'id_user'; 
    public $incrementing = false;  
    
    // Enkripsi password saat menyimpan data
    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if ($user->password) {
                // Enkripsi password jika disediakan
                $user->password = bcrypt($user->password);
            }
        });
    }
}
