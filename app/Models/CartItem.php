<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    // Kolom yang bisa diisi massal
    protected $fillable = [
        'id_produk',
        'id_service',
        'jumlah',
        'harga',
        'harga_setelah_diskon',
    ];

    // Relasi dengan tabel produk
    public function product()
    {
        return $this->belongsTo(Item::class, 'id_produk', 'id_produk');
    }

    // Relasi dengan tabel layanan
    public function service()
    {
        return $this->belongsTo(Service::class, 'id_service', 'id_service');
    }
}
