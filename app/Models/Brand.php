<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'brands';

    // Primary key
    protected $primaryKey = 'id_brand';

    // Tipe data primary key
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom yang dapat diisi (fillable)
    protected $fillable = ['id_brand', 'brand'];

    // Jika Anda menggunakan timestamp (created_at dan updated_at)
    public $timestamps = true;
}
