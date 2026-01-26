<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'services';

    // Primary key
    protected $primaryKey = 'id_service';

    // Tipe data primary key
    public $incrementing = false;
    protected $keyType = 'string'; 

    // Kolom yang dapat diisi (fillable)
    protected $fillable = ['id_service', 'service', 'harga_jual','diskon','harga_setelah_diskon'];

    // Jika Anda menggunakan timestamp (created_at dan updated_at)
    public $timestamps = true;


}
