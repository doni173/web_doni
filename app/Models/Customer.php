<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    
    protected $table = 'customers';
    protected $primaryKey = 'id_pelanggan';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id_pelanggan',
        'nama_pelanggan',
        'no_hp',
    ];
    
    public $timestamps = true;
}