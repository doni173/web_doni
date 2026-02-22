<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // Nama tabel jika berbeda dari plural default
    protected $table = 'categories';

    // Primary key
    protected $primaryKey = 'id_kategori';

    // Tipe data primary key
    public $incrementing = false;
    protected $keyType = 'string';

    // Kolom yang dapat diisi (fillable)
    protected $fillable = ['id_kategori', 'kategori'];
}